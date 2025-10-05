<?php

namespace App\Events;

use App\Models\FuelSchedule;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CroAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $fuelSchedule;

    public function __construct(FuelSchedule $fuelSchedule)
    {
        $this->fuelSchedule = $fuelSchedule->load(['flight', 'cro', 'shift']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user.' . $this->fuelSchedule->cro_id),
            new Channel('supervisors'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'cro.assigned';
    }

    public function broadcastWith(): array
    {
        $data = [
            'id' => $this->fuelSchedule->id,
            'flight_number' => $this->fuelSchedule->flight->flight_number,
            'airline_code' => $this->fuelSchedule->flight->airline_code,
            'departure_airport' => $this->fuelSchedule->flight->departure_airport,
            'arrival_airport' => $this->fuelSchedule->flight->arrival_airport,
            'scheduled_fueling_time' => $this->fuelSchedule->scheduled_fueling_time->toISOString(),
            'cro_name' => $this->fuelSchedule->cro->name,
            'shift_name' => $this->fuelSchedule->shift->name,
            'message' => "You have been assigned to fuel Flight {$this->fuelSchedule->flight->flight_number}",
            'timestamp' => now()->toISOString(),
            'type' => 'assignment'
        ];

        // Juga kirim push notification via FCM
        $this->sendFcmNotification($data);

        return $data;
    }

    private function sendFcmNotification($data)
    {
        try {
            // Kirim ke CRO yang di-assign
            if ($this->fuelSchedule->cro) {
                $this->fuelSchedule->cro->notify(new \App\Notifications\CroAssigned($this->fuelSchedule));
            }

            // Juga kirim ke supervisor
            $supervisors = \App\Models\User::where('role', 'Pengawas')->get();
            foreach ($supervisors as $supervisor) {
                $supervisor->notify(new \App\Notifications\SupervisorAssignment($this->fuelSchedule));
            }

        } catch (\Exception $e) {
            Log::error('FCM notification failed: ' . $e->getMessage());
        }
    }
}