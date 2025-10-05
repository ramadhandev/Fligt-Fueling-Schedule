<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'fuel_schedule_id',
        'type',
        'channel',
        'message',
        'sent_at',
        'success'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'success' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fuelSchedule(): BelongsTo
    {
        return $this->belongsTo(FuelSchedule::class);
    }
}