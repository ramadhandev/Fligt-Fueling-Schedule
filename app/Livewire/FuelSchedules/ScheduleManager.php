<?php

namespace App\Livewire\FuelSchedules;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FuelSchedule;
use App\Models\User;
use App\Models\Shift;
use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\CroAssigned;
use App\Events\FuelStatusUpdated;

class ScheduleManager extends Component
{
    use WithPagination;

    // Modal properties
    public $showAssignModal = false;
    public $showStatusModal = false;
    
    // Assignment properties
    public $assignScheduleId;
    public $assignCroId;
    public $assignShiftId;
    
    // Status properties
    public $statusScheduleId;
    public $newStatus;
    public $actualFuelingTime;

    // Filter properties
    public $date = '';
    public $status = '';
    public $shift = '';
    public $cro = '';

    // Available statuses
    public $statuses = ['Pending', 'InProgress', 'Completed', 'Delayed'];

    protected $rules = [
        'assignCroId' => 'required|exists:users,id',
        'assignShiftId' => 'required|exists:shifts,id',
        'newStatus' => 'required|in:Pending,InProgress,Completed,Delayed',
        'actualFuelingTime' => 'nullable|date',
    ];

    public function mount()
    {
        // Inisialisasi data jika diperlukan
    }

    // Get current authenticated user
    public function getUserProperty()
    {
        return Auth::user();
    }

    // Get statistics
    public function getStatsProperty()
    {
        $query = FuelSchedule::query();

        // Apply filters for stats
        if ($this->date) {
            $query->whereDate('scheduled_fueling_time', $this->date);
        }

        return [
            'total_schedules' => $query->count(),
            'pending' => (clone $query)->where('status', 'Pending')->count(),
            'in_progress' => (clone $query)->where('status', 'InProgress')->count(),
            'completed' => (clone $query)->where('status', 'Completed')->count(),
            'delayed' => (clone $query)->where('status', 'Delayed')->count(),
        ];
    }

    public function openAssignModal($scheduleId)
    {
        $this->assignScheduleId = $scheduleId;
        $this->showAssignModal = true;
        $this->resetValidation();
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->reset(['assignScheduleId', 'assignCroId', 'assignShiftId']);
    }

    public function openStatusModal($scheduleId)
    {
        $schedule = FuelSchedule::findOrFail($scheduleId);
        
        $this->statusScheduleId = $scheduleId;
        $this->newStatus = $schedule->status;
        $this->actualFuelingTime = $schedule->actual_fueling_time?->format('Y-m-d\TH:i');
        $this->showStatusModal = true;
        $this->resetValidation();
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->reset(['statusScheduleId', 'newStatus', 'actualFuelingTime']);
    }

    public function assignCRO()
    {
        $this->validate([
            'assignCroId' => 'required|exists:users,id',
            'assignShiftId' => 'required|exists:shifts,id',
        ]);

        try {
            $schedule = FuelSchedule::with(['flight', 'cro'])->findOrFail($this->assignScheduleId);
            
            $oldCro = $schedule->cro;
            
            $schedule->update([
                'cro_id' => $this->assignCroId,
                'shift_id' => $this->assignShiftId,
            ]);

            // Refresh data
            $schedule->refresh()->load(['flight', 'cro', 'shift']);

            // ✅ Simpan notifikasi dengan trigger sound
            $this->saveAppNotification($schedule, 'assignment', $oldCro);

            // ✅ Broadcast real-time dengan data notifikasi
            broadcast(new CroAssigned($schedule));

            session()->flash('success', 'CRO assigned successfully!');
            $this->closeAssignModal();
            
        } catch (\Exception $e) {
            Log::error('Assign CRO failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to assign CRO.');
        }
    }

    public function updateStatus()
    {
        $this->validate([
            'newStatus' => 'required|in:Pending,InProgress,Completed,Delayed',
            'actualFuelingTime' => 'nullable|date',
        ]);

        try {
            $schedule = FuelSchedule::with(['flight', 'cro'])->findOrFail($this->statusScheduleId);
            
            $oldStatus = $schedule->status;
            $updateData = ['status' => $this->newStatus];
            
            if ($this->actualFuelingTime) {
                $updateData['actual_fueling_time'] = $this->actualFuelingTime;
            }
            if ($this->newStatus === 'Completed' && !$this->actualFuelingTime) {
                $updateData['actual_fueling_time'] = now();
            }

            $schedule->update($updateData);
            $schedule->refresh()->load(['flight', 'cro', 'shift']);

            // ✅ SIMPAN NOTIFIKASI STATUS UPDATE UNTUK CRS & PENGAWAS
            // Tentukan siapa yang melakukan update
            $updatedBy = Auth::user();
            
            if (in_array($this->newStatus, ['InProgress', 'Completed', 'Delayed'])) {
                $this->saveStatusNotification($schedule, $oldStatus, $updatedBy);
                broadcast(new FuelStatusUpdated($schedule, $oldStatus));
            }

            session()->flash('success', 'Status updated successfully!');
            $this->closeStatusModal();
            
        } catch (\Exception $e) {
            Log::error('Update status failed: ' . $e->getMessage());
            session()->flash('error', 'Failed to update status.');
        }
    }

    public function resetFilters()
    {
        $this->reset(['date', 'status', 'shift', 'cro']);
        $this->resetPage();
    }

    private function saveAppNotification(FuelSchedule $schedule, $type, $oldCro = null)
    {
        try {
            $data = [
                'flight_number' => $schedule->flight->flight_number ?? 'N/A',
                'airline_code' => $schedule->flight->airline_code ?? 'N/A',
                'departure_airport' => $schedule->flight->departure_airport ?? 'N/A',
                'arrival_airport' => $schedule->flight->arrival_airport ?? 'N/A',
                'scheduled_fueling_time' => $schedule->scheduled_fueling_time?->format('Y-m-d H:i'),
                'shift_name' => $schedule->shift->name ?? 'N/A',
                'old_cro_name' => $oldCro ? $oldCro->name : null,
                'trigger_sound' => true,
                'notification_type' => 'assignment',
            ];

            // Notifikasi untuk CRO yang di-assign (dengan sound)
            if ($schedule->cro_id) {
                AppNotification::create([
                    'user_id' => $schedule->cro_id,
                    'fuel_schedule_id' => $schedule->id,
                    'type' => $type,
                    'title' => 'Penugasan Pengisian Bahan Bakar Baru',
                    'message' => "Anda ditugaskan untuk mengisi bahan bakar Flight {$data['flight_number']}",
                    'data' => $data,
                    'sound_played' => false,
                ]);
            }

            // Notifikasi untuk supervisor (tanpa sound)
            $supervisors = User::where('role', 'Pengawas')->get();
            foreach ($supervisors as $supervisor) {
                AppNotification::create([
                    'user_id' => $supervisor->id,
                    'fuel_schedule_id' => $schedule->id,
                    'type' => 'supervisor_assignment',
                    'title' => 'Penugasan CRO',
                    'message' => "CRO {$schedule->cro->name} ditugaskan ke Flight {$data['flight_number']}",
                    'data' => $data,
                    'sound_played' => true,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Save app notification failed: ' . $e->getMessage());
        }
    }

    // ✅ METHOD YANG DIPERBAIKI: Save notification untuk status update
    private function saveStatusNotification(FuelSchedule $schedule, $oldStatus, $updatedBy)
    {
        try {
            $data = [
                'flight_number' => $schedule->flight->flight_number ?? 'N/A',
                'airline_code' => $schedule->flight->airline_code ?? 'N/A',
                'departure_airport' => $schedule->flight->departure_airport ?? 'N/A',
                'arrival_airport' => $schedule->flight->arrival_airport ?? 'N/A',
                'old_status' => $oldStatus,
                'new_status' => $schedule->status,
                'actual_fueling_time' => $schedule->actual_fueling_time?->format('Y-m-d H:i'),
                'scheduled_fueling_time' => $schedule->scheduled_fueling_time?->format('Y-m-d H:i'),
                'trigger_sound' => true,
                'notification_type' => 'status_update',
                'cro_name' => $schedule->cro->name ?? 'Unknown CRO',
                'shift_name' => $schedule->shift->name ?? 'N/A',
                'updated_by' => $updatedBy->name,
                'updated_by_role' => $updatedBy->role,
            ];

            $statusMessages = [
                'InProgress' => 'sedang diproses',
                'Completed' => 'telah selesai',
                'Delayed' => 'tertunda'
            ];

            // Tentukan message berdasarkan siapa yang update
            if ($updatedBy->role === 'CRO') {
                $message = "CRO {$data['cro_name']} mengupdate status Flight {$data['flight_number']} menjadi {$statusMessages[$schedule->status]}";
            } else {
                $message = "{$data['updated_by']} ({$data['updated_by_role']}) mengupdate status Flight {$data['flight_number']} menjadi {$statusMessages[$schedule->status]}";
            }

            // ✅ LOGIC BARU: Tentukan siapa yang dapat notifikasi
            if ($updatedBy->role === 'CRO') {
                // Jika CRO yang update, kirim notifikasi ke CRS & Pengawas
                $this->notifyCrsAndSupervisors($schedule, $message, $data);
            } else {
                // Jika CRS/Pengawas yang update, kirim notifikasi ke CRO & lainnya
                $this->notifyCroAndOthers($schedule, $message, $data, $updatedBy);
            }

            Log::info('Status notification sent', [
                'schedule_id' => $schedule->id,
                'flight_number' => $data['flight_number'],
                'updated_by' => $updatedBy->name,
                'updated_by_role' => $updatedBy->role,
                'old_status' => $oldStatus,
                'new_status' => $schedule->status
            ]);

        } catch (\Exception $e) {
            Log::error('Save status notification failed: ' . $e->getMessage());
        }
    }

    // ✅ METHOD BARU: Notifikasi ketika CRO update status
    private function notifyCrsAndSupervisors(FuelSchedule $schedule, $message, $data)
    {
        // ✅ NOTIFIKASI UNTUK SEMUA CRS
        $crsUsers = User::where('role', 'CRS')->get();
        foreach ($crsUsers as $crs) {
            AppNotification::create([
                'user_id' => $crs->id,
                'fuel_schedule_id' => $schedule->id,
                'type' => 'status_update',
                'title' => 'Update Status Pengisian',
                'message' => $message,
                'data' => $data,
                'sound_played' => false, // ✅ Trigger audio untuk CRS
            ]);
        }

        // ✅ NOTIFIKASI UNTUK SEMUA PENGAWAS
        $supervisors = User::where('role', 'Pengawas')->get();
        foreach ($supervisors as $supervisor) {
            AppNotification::create([
                'user_id' => $supervisor->id,
                'fuel_schedule_id' => $schedule->id,
                'type' => 'status_update',
                'title' => 'Update Status Pengisian',
                'message' => $message,
                'data' => $data,
                'sound_played' => false, // ✅ Trigger audio untuk Pengawas
            ]);
        }

        // ❌ TIDAK KIRIM NOTIFIKASI KE CRO YANG UPDATE
        // Karena CRO sudah tahu action yang dia lakukan
    }

    // ✅ METHOD BARU: Notifikasi ketika CRS/Pengawas update status
    private function notifyCroAndOthers(FuelSchedule $schedule, $message, $data, $updatedBy)
    {
        // ✅ NOTIFIKASI UNTUK CRO YANG BERTUGAS
        if ($schedule->cro_id) {
            AppNotification::create([
                'user_id' => $schedule->cro_id,
                'fuel_schedule_id' => $schedule->id,
                'type' => 'status_update',
                'title' => 'Update Status Pengisian',
                'message' => $message,
                'data' => $data,
                'sound_played' => false, // ✅ Trigger audio untuk CRO
            ]);
        }

        // ✅ NOTIFIKASI UNTUK PENGAWAS LAIN (jika yang update bukan pengawas)
        if ($updatedBy->role !== 'Pengawas') {
            $supervisors = User::where('role', 'Pengawas')->get();
            foreach ($supervisors as $supervisor) {
                // Skip jika pengawas ini yang update
                if ($supervisor->id === $updatedBy->id) continue;
                
                AppNotification::create([
                    'user_id' => $supervisor->id,
                    'fuel_schedule_id' => $schedule->id,
                    'type' => 'status_update',
                    'title' => 'Update Status Pengisian',
                    'message' => $message,
                    'data' => $data,
                    'sound_played' => false,
                ]);
            }
        }

        // ✅ NOTIFIKASI UNTUK CRS LAIN (jika yang update bukan CRS)
        if ($updatedBy->role !== 'CRS') {
            $crsUsers = User::where('role', 'CRS')->get();
            foreach ($crsUsers as $crs) {
                // Skip jika CRS ini yang update
                if ($crs->id === $updatedBy->id) continue;
                
                AppNotification::create([
                    'user_id' => $crs->id,
                    'fuel_schedule_id' => $schedule->id,
                    'type' => 'status_update',
                    'title' => 'Update Status Pengisian',
                    'message' => $message,
                    'data' => $data,
                    'sound_played' => false,
                ]);
            }
        }
    }

    public function render()
    {
        $query = FuelSchedule::with(['flight', 'cro', 'shift']);

        // Apply filters
        if ($this->date) {
            $query->whereDate('scheduled_fueling_time', $this->date);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->shift) {
            $query->where('shift_id', $this->shift);
        }

        if ($this->cro) {
            $query->where('cro_id', $this->cro);
        }

        // Jika user adalah CRO, hanya tampilkan jadwal mereka
        if ($this->user && $this->user->role === 'CRO') {
            $query->where('cro_id', $this->user->id);
        }

        $schedules = $query->orderBy('scheduled_fueling_time', 'desc')
            ->paginate(10);

        $cros = User::where('role', 'CRO')->get();
        $shifts = Shift::all();

        return view('livewire.fuel-schedules.schedule-manager', [
            'schedules' => $schedules,
            'cros' => $cros,
            'shifts' => $shifts,
            'stats' => $this->stats,
        ]);
    }
}