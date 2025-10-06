<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_number',
        'airline_code',
        'departure_airport',
        'arrival_airport',
        'scheduled_departure', // Hanya STD
        'status',
        'created_by',
    ];

    protected $casts = [
        'scheduled_departure' => 'datetime',
        // HAPUS scheduled_arrival dari casts
    ];

    protected $attributes = [
        'status' => 'Scheduled',
    ];

    public function fuelSchedule(): HasOne
    {
        return $this->hasOne(FuelSchedule::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}