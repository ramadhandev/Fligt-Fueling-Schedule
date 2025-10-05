<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // CRS, Pengawas, CRO
        'phone_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            
        ];
    }

    // Relationships
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class, 'pengawas_id');
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class, 'created_by');
    }

    public function fuelSchedules(): HasMany
    {
        return $this->hasMany(FuelSchedule::class, 'cro_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Simple role check methods
    public function getIsCRSAttribute()
    {
        return $this->role === 'CRS';
    }

    public function getIsPengawasAttribute()
    {
        return $this->role === 'Pengawas';
    }

    public function getIsCROAttribute()
    {
        return $this->role === 'CRO';
    }

    // Check jika user bisa menerima WhatsApp
    public function getCanReceiveWhatsAppAttribute()
    {
        return $this->is_whatsapp_opt_in && !empty($this->phone_number);
    }
   public function routeNotificationForFcm($notification)
    {
        // Return FCM tokens untuk user ini
        return \App\Models\FcmToken::where('user_id', $this->id)
            ->pluck('token')
            ->toArray();
    }

     public function appNotifications()
    {
        return $this->hasMany(\App\Models\AppNotification::class);
    }
    
}