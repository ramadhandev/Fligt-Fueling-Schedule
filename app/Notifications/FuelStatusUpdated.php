<?php

namespace App\Notifications;

use App\Models\FuelSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FuelStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public $fuelSchedule;
    public $oldStatus;

    public function __construct(FuelSchedule $fuelSchedule, $oldStatus = null)
    {
        $this->fuelSchedule = $fuelSchedule;
        $this->oldStatus = $oldStatus;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'status_update',
            'title' => 'Fuel Status Updated',
            'message' => "Flight {$this->fuelSchedule->flight->flight_number} status changed from {$this->oldStatus} to {$this->fuelSchedule->status}",
            'schedule_id' => $this->fuelSchedule->id,
            'flight_number' => $this->fuelSchedule->flight->flight_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->fuelSchedule->status,
            'actual_fueling_time' => $this->fuelSchedule->actual_fueling_time,
            'timestamp' => now(),
        ];
    }
}