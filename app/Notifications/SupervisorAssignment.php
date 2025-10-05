<?php

namespace App\Notifications;

use App\Models\FuelSchedule;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisorAssignment extends Notification implements ShouldQueue
{
    use Queueable;

    public $fuelSchedule;
    public $oldCro;

    public function __construct(FuelSchedule $fuelSchedule, $oldCro = null)
    {
        $this->fuelSchedule = $fuelSchedule;
        $this->oldCro = $oldCro;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $message = "CRO {$this->fuelSchedule->cro->name} assigned to Flight {$this->fuelSchedule->flight->flight_number}";
        
        if ($this->oldCro && $this->oldCro->id != $this->fuelSchedule->cro_id) {
            $message = "Flight {$this->fuelSchedule->flight->flight_number} reassigned from {$this->oldCro->name} to {$this->fuelSchedule->cro->name}";
        }

        return [
            'type' => 'supervisor_assignment',
            'title' => 'CRO Assignment',
            'message' => $message,
            'schedule_id' => $this->fuelSchedule->id,
            'flight_number' => $this->fuelSchedule->flight->flight_number,
            'cro_name' => $this->fuelSchedule->cro->name,
            'old_cro_name' => $this->oldCro ? $this->oldCro->name : null,
            'shift_name' => $this->fuelSchedule->shift->name,
            'timestamp' => now(),
        ];
    }
}