<?php

namespace App\Notifications;

use App\Models\FuelSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CroAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $fuelSchedule;

    public function __construct(FuelSchedule $fuelSchedule)
    {
        $this->fuelSchedule = $fuelSchedule;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'assignment',
            'title' => 'New Fuel Assignment',
            'message' => "You have been assigned to fuel Flight {$this->fuelSchedule->flight->flight_number}",
            'schedule_id' => $this->fuelSchedule->id,
            'flight_number' => $this->fuelSchedule->flight->flight_number,
            'airline_code' => $this->fuelSchedule->flight->airline_code,
            'departure_airport' => $this->fuelSchedule->flight->departure_airport,
            'arrival_airport' => $this->fuelSchedule->flight->arrival_airport,
            'scheduled_fueling_time' => $this->fuelSchedule->scheduled_fueling_time,
            'shift_name' => $this->fuelSchedule->shift->name,
            'timestamp' => now(),
        ];
    }
}