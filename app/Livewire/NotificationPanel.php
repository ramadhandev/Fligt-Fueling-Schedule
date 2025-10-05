<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AppNotification;
use App\Models\FuelSchedule;
use Illuminate\Support\Facades\Auth;

class NotificationPanel extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $totalNotifications = 0;
    public $showPanel = false;
    public $hasNewSoundNotification = false;

    protected $listeners = [
        'refreshNotifications' => 'loadNotifications',
        'markAsRead' => 'markAsRead',
        'playNotificationSound' => 'playSound'
    ];

    public function mount()
    {
        $this->loadNotifications();
        $this->checkSoundNotifications();
    }

    public function loadNotifications()
    {
        if (Auth::check()) {
            $userId = Auth::id();
            
            // Notifikasi untuk panel (10 terbaru)
            $this->notifications = AppNotification::with(['fuelSchedule.flight', 'user'])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            // Hitung total notifikasi
            $this->totalNotifications = AppNotification::where('user_id', $userId)->count();
            
            // Hitung notifikasi belum dibaca
            $this->unreadCount = AppNotification::where('user_id', $userId)
                ->whereNull('read_at')
                ->count();

            // Check untuk sound notifications setelah load
            $this->checkSoundNotifications();
        } else {
            $this->notifications = collect();
            $this->unreadCount = 0;
            $this->totalNotifications = 0;
        }
    }

    public function checkSoundNotifications()
    {
        if (Auth::check()) {
            $soundNotifications = AppNotification::getNotificationsWithSound(Auth::id());
            $this->hasNewSoundNotification = $soundNotifications->count() > 0;
            
            if ($this->hasNewSoundNotification) {
                $this->dispatch('play-notification-sound');
                
                // Mark sound as played
                foreach ($soundNotifications as $notification) {
                    $notification->markSoundPlayed();
                }
                
                $this->hasNewSoundNotification = false;
            }
        }
    }

    // ✅ METHOD BARU: Handle klik notifikasi
    public function handleNotificationClick($notificationId, $scheduleId, $type)
    {
        // Mark as read
        $this->markAsRead($notificationId);
        
        // Jika ada scheduleId, redirect ke halaman yang sesuai
        if ($scheduleId) {
            $this->redirectToSchedule($scheduleId);
        }
        
        $this->showPanel = false; // Tutup panel
    }

    public function markAsRead($notificationId)
    {
        if (!Auth::check()) {
            return;
        }

        $notification = AppNotification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->first();
        
        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->dispatch('notificationsUpdated');
        }
    }

    public function markAllAsRead()
    {
        if (!Auth::check()) {
            return;
        }

        AppNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        $this->loadNotifications();
        $this->dispatch('notificationsUpdated');
    }

    public function togglePanel()
    {
        $this->showPanel = !$this->showPanel;
        if ($this->showPanel) {
            $this->loadNotifications();
        }
    }

    public function playSound()
    {
        $this->dispatch('play-notification-sound');
    }

    // ✅ METHOD BARU: Redirect ke schedule
    private function redirectToSchedule($scheduleId)
    {
        // Redirect ke halaman jadwal sesuai role
        if (Auth::user()->role === 'CRO') {
            return redirect()->route('my-schedules');
        } else {
            // Untuk CRS/Pengawas, redirect ke fuel schedules manager
            return redirect()->route('fuel-schedules.manager');
        }
    }

    // ✅ METHOD BARU: Lihat Semua Notifikasi
    public function viewAllNotifications()
    {
        $this->showPanel = false; // Tutup panel
        
        // Redirect ke halaman semua notifikasi
        return redirect()->route('notifications.index');
    }

    public function render()
    {
        return view('livewire.notification-panel');
    }
}