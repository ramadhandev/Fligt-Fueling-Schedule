<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'fuel_schedule_id', 
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'sound_played', // ✅ FIELD BARU untuk tracking audio
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sound_played' => 'boolean', // ✅ CAST BARU
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fuelSchedule(): BelongsTo
    {
        return $this->belongsTo(FuelSchedule::class);
    }

    // Scope untuk notifikasi belum dibaca
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // ✅ SCOPE BARU: notifikasi yang sound belum diputar
    public function scopeUnplayedSound($query)
    {
        return $query->where('sound_played', false);
    }

    // Mark as read
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    // ✅ METHOD BARU: Mark sound as played
    public function markSoundPlayed()
    {
        $this->update(['sound_played' => true]);
    }

    // ✅ METHOD BARU: Get badge count untuk user
    public static function getBadgeCount($userId)
    {
        return static::where('user_id', $userId)
            ->unread()
            ->count();
    }

    // ✅ METHOD BARU: Get notifications with sound
    public static function getNotificationsWithSound($userId)
    {
        return static::where('user_id', $userId)
            ->unplayedSound()
            ->with('fuelSchedule.flight')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}