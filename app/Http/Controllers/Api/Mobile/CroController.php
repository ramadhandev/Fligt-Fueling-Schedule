<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\FuelSchedule;
use App\Models\User;
use App\Models\AppNotification;
use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class CroController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cari user dengan role CRO
        $user = User::where('email', $request->email)
                    ->where('role', 'CRO')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Create token
        $token = $user->createToken('cro-mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone_number' => $user->phone_number,
            ]
        ]);
    }

    // ✅ NEW: Dashboard data dengan stats seperti Livewire component
    public function getDashboard(Request $request)
    {
        $user = Auth::user();
        
        // Stats seperti di Livewire StatsOverview
        $stats = [
            'my_schedules' => FuelSchedule::where('cro_id', $user->id)
                ->whereDate('scheduled_fueling_time', today())
                ->count(),
            'my_pending' => FuelSchedule::where('cro_id', $user->id)
                ->where('status', 'Pending')
                ->count(),
            'my_completed' => FuelSchedule::where('cro_id', $user->id)
                ->where('status', 'Completed')
                ->count(),
            'my_in_progress' => FuelSchedule::where('cro_id', $user->id)
                ->where('status', 'InProgress')
                ->count(),
        ];

        // Today's schedules for quick overview
        $todaySchedules = FuelSchedule::with(['flight', 'shift'])
            ->where('cro_id', $user->id)
            ->whereDate('scheduled_fueling_time', today())
            ->orderBy('scheduled_fueling_time', 'asc')
            ->take(5)
            ->get();

        // Recent notifications
        $recentNotifications = AppNotification::with(['fuelSchedule.flight'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'today_schedules' => $todaySchedules,
                'recent_notifications' => $recentNotifications,
                'current_time' => now()->toISOString(),
            ]
        ]);
    }

    // ✅ Get all schedules (existing - diperbaiki response)
    public function getSchedules(Request $request)
    {
        $user = Auth::user();
        
        $query = FuelSchedule::with(['flight', 'shift'])
            ->where('cro_id', $user->id);

        // Apply filters dari request
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('scheduled_fueling_time', $request->date);
        }

        $schedules = $query->orderBy('scheduled_fueling_time', 'asc')
            ->get();

        // Calculate stats seperti di Livewire
        $todaySchedules = $schedules->filter(function ($schedule) {
            return $schedule->scheduled_fueling_time->isToday();
        });

        $stats = [
            'total' => $schedules->count(),
            'total_today' => $todaySchedules->count(),
            'pending' => $schedules->where('status', 'Pending')->count(),
            'completed' => $schedules->where('status', 'Completed')->count(),
            'in_progress' => $schedules->where('status', 'InProgress')->count(),
            'delayed' => $schedules->where('status', 'Delayed')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'schedules' => $schedules,
                'stats' => $stats,
                'filters' => $request->only(['status', 'date'])
            ]
        ]);
    }

    // ✅ NEW: Get today's schedules only
    public function getTodaySchedules(Request $request)
    {
        $user = Auth::user();
        
        $schedules = FuelSchedule::with(['flight', 'shift'])
            ->where('cro_id', $user->id)
            ->whereDate('scheduled_fueling_time', today())
            ->orderBy('scheduled_fueling_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    // ✅ Update status (existing - diperbaiki response)
    public function updateStatus(Request $request, FuelSchedule $schedule)
    {
        $request->validate([
            'status' => 'required|in:Pending,InProgress,Completed,Delayed',
            'actual_fueling_time' => 'nullable|date',
        ]);

        // Check if schedule belongs to authenticated CRO
        if ($schedule->cro_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this schedule'
            ], 403);
        }

        $oldStatus = $schedule->status;
        $updateData = ['status' => $request->status];

        // Set actual fueling time if completed atau dari input
        if ($request->status === 'Completed' && !$request->actual_fueling_time) {
            $updateData['actual_fueling_time'] = now();
        } elseif ($request->actual_fueling_time) {
            $updateData['actual_fueling_time'] = $request->actual_fueling_time;
        }

        $schedule->update($updateData);
        $schedule->refresh()->load(['flight', 'shift']);

        // ✅ KIRIM NOTIFIKASI KE CRS & PENGAWAS (seperti di Livewire)
        $this->sendStatusNotification($schedule, $oldStatus);

        // Log the status change
        Log::info('CRO mobile status update', [
            'cro_id' => Auth::id(),
            'schedule_id' => $schedule->id,
            'flight_number' => $schedule->flight->flight_number,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $schedule
        ]);
    }

    // ✅ Get profile (existing)
    public function getProfile(Request $request)
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    // ✅ Get notifications (existing - diperbaiki response)
    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        
        $notifications = AppNotification::with(['fuelSchedule.flight'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $unreadCount = AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]
        ]);
    }

    // ✅ Mark notification as read (existing - diperbaiki response)
    public function markNotificationAsRead(Request $request, $notificationId)
    {
        $user = Auth::user();
        
        $notification = AppNotification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    // ✅ NEW: Mark all notifications as read
    public function markAllNotificationsAsRead(Request $request)
    {
        $user = Auth::user();
        
        $updated = AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} notifications marked as read",
            'data' => [
                'marked_read_count' => $updated
            ]
        ]);
    }

    // ✅ METHOD: Kirim notifikasi status update ke CRS & Pengawas (existing)
    private function sendStatusNotification(FuelSchedule $schedule, $oldStatus)
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
            ];

            $statusMessages = [
                'InProgress' => 'sedang diproses',
                'Completed' => 'telah selesai',
                'Delayed' => 'tertunda'
            ];

            $message = "CRO {$data['cro_name']} mengupdate status Flight {$data['flight_number']} menjadi {$statusMessages[$schedule->status]}";

            // ✅ NOTIFIKASI UNTUK SEMUA USER CRS
            $crsUsers = User::where('role', 'CRS')->get();
            foreach ($crsUsers as $crs) {
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

            // ✅ NOTIFIKASI UNTUK SEMUA USER PENGAWAS
            $supervisors = User::where('role', 'Pengawas')->get();
            foreach ($supervisors as $supervisor) {
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

            Log::info('Status notifications sent successfully from mobile', [
                'schedule_id' => $schedule->id,
                'flight_number' => $data['flight_number'],
                'total_notifications' => $crsUsers->count() + $supervisors->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Send status notification from mobile failed: ' . $e->getMessage());
        }
    }
}