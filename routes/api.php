<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\CroController;
use App\Http\Controllers\Api\Mobile\NotificationController;
use App\Http\Controllers\Api\Mobile\MobileProfileController;

Route::get('/test', function () {
    return response()->json(['success' => true, 'message' => 'API working']);
});

// Public routes
Route::post('/mobile/login', [CroController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // CRO specific routes
    Route::prefix('mobile')->group(function () {
        // Dashboard & Schedules
        Route::get('/dashboard', [CroController::class, 'getDashboard']);
        Route::get('/schedules', [CroController::class, 'getSchedules']);
        Route::get('/schedules/today', [CroController::class, 'getTodaySchedules']);
        Route::put('/schedules/{schedule}/status', [CroController::class, 'updateStatus']);
        
        // Profile
        Route::get('/profile', [MobileProfileController::class, 'show']);
        Route::put('/profile', [MobileProfileController::class, 'update']);
        
        // Notifications - Complete endpoints
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/{id}/mark-sound-played', [NotificationController::class, 'markSoundPlayed']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::get('/unplayed-sound', [NotificationController::class, 'getUnplayedSoundNotifications']);
            Route::post('/fcm-token', [NotificationController::class, 'storeToken']);
        });
    });
});

// Fallback for undefined API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});