<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'cro_id',
        'shift_id',
        'scheduled_fueling_time',
        'actual_fueling_time',
        'status',
    ];

    protected $casts = [
        'scheduled_fueling_time' => 'datetime',
        'actual_fueling_time' => 'datetime',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function cro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cro_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}