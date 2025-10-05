<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function storeToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_type' => 'required|string|in:ios,android',
        ]);

        $user = Auth::user();

        // Update or create FCM token
        FcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_type' => $request->device_type,
            ],
            [
                'token' => $request->fcm_token,
            ]
        );

        return response()->json(['message' => 'FCM token stored successfully']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = AppNotification::where('user_id', $user->id)
            ->with(['fuelSchedule.flight', 'fuelSchedule.shift'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => (string) $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $this->mapNotificationType($notification->type),
                    'read' => !is_null($notification->read_at),
                    'sound_played' => (bool) $notification->sound_played,
                    'created_at' => $notification->created_at->toISOString(),
                    'schedule_id' => $notification->fuel_schedule_id ? (string) $notification->fuel_schedule_id : null,
                    'data' => $notification->data,
                    'flight_info' => $notification->fuelSchedule->flight ?? null,
                    'shift_info' => $notification->fuelSchedule->shift ?? null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();

        $notification = AppNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'id' => (string) $notification->id,
                'read' => true,
                'read_at' => $notification->read_at?->toISOString(),
            ]
        ]);
    }

    public function markSoundPlayed($id)
    {
        $user = Auth::user();

        $notification = AppNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markSoundPlayed();

        return response()->json([
            'success' => true,
            'message' => 'Sound marked as played',
            'data' => [
                'id' => (string) $notification->id,
                'sound_played' => true,
            ]
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    public function unreadCount()
    {
        $user = Auth::user();

        $count = AppNotification::getBadgeCount($user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    public function getUnplayedSoundNotifications()
    {
        $user = Auth::user();

        $notifications = AppNotification::getNotificationsWithSound($user->id)
            ->map(function ($notification) {
                return [
                    'id' => (string) $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $this->mapNotificationType($notification->type),
                    'read' => !is_null($notification->read_at),
                    'sound_played' => (bool) $notification->sound_played,
                    'created_at' => $notification->created_at->toISOString(),
                    'schedule_id' => $notification->fuel_schedule_id ? (string) $notification->fuel_schedule_id : null,
                    'data' => $notification->data,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    private function mapNotificationType($backendType)
    {
        $typeMap = [
            'assignment' => 'new_assignment',
            'status_update' => 'schedule_reminder',
            'reminder' => 'schedule_reminder',
            'system' => 'system_alert',
        ];

        return $typeMap[$backendType] ?? 'system_alert';
    }
}