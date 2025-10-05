<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Flight;
use App\Models\FuelSchedule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends Component
{
    public $stats = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $user = Auth::user();
        
        if ($user->role === 'CRS') {
            $this->stats = [
                'total_flights' => Flight::count(),
                'today_flights' => Flight::whereDate('scheduled_departure', today())->count(),
                'pending_schedules' => FuelSchedule::where('status', 'Pending')->count(),
                'completed_schedules' => FuelSchedule::where('status', 'Completed')->count(),
            ];
        } elseif ($user->role === 'Pengawas') {
            $this->stats = [
                'total_operators' => User::where('role', 'CRO')->count(),
                'active_schedules' => FuelSchedule::whereIn('status', ['Pending', 'InProgress'])->count(),
                'today_completed' => FuelSchedule::where('status', 'Completed')
                    ->whereDate('updated_at', today())
                    ->count(),
            ];
        } elseif ($user->role === 'CRO') {
            $this->stats = [
                'my_schedules' => FuelSchedule::where('cro_id', $user->id)
                    ->whereDate('scheduled_fueling_time', today())
                    ->count(),
                'my_pending' => FuelSchedule::where('cro_id', $user->id)
                    ->where('status', 'Pending')
                    ->count(),
                'my_completed' => FuelSchedule::where('cro_id', $user->id)
                    ->where('status', 'Completed')
                    ->count(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.dashboard.stats-overview');
    }
}