<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\FuelSchedule;
use App\Models\User;

class FuelStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $schedule;
    public $oldStatus;

    public function __construct(FuelSchedule $schedule, $oldStatus)
    {
        $this->schedule = $schedule;
        $this->oldStatus = $oldStatus;
    }

    public function broadcastOn()
    {
        // ✅ BROADCAST KE CHANNEL UNTUK CRS & PENGAWAS
        $channels = [];
        
        // Channel untuk semua CRS
        $crsUsers = User::where('role', 'CRS')->get();
        foreach ($crsUsers as $crs) {
            $channels[] = new PrivateChannel('notifications.' . $crs->id);
        }
        
        // Channel untuk semua Pengawas
        $supervisors = User::where('role', 'Pengawas')->get();
        foreach ($supervisors as $supervisor) {
            $channels[] = new PrivateChannel('notifications.' . $supervisor->id);
        }
        
        return $channels;
    }

    public function broadcastAs()
    {
        return 'fuel.status.updated';
    }

    public function broadcastWith()
    {
        return [
            'schedule_id' => $this->schedule->id,
            'flight_number' => $this->schedule->flight->flight_number ?? 'N/A',
            'old_status' => $this->oldStatus,
            'new_status' => $this->schedule->status,
            'cro_name' => $this->schedule->cro->name ?? 'Unknown CRO',
            'message' => 'Status pengisian telah diupdate'
        ];
    }
}