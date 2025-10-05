<?php
// routes/web.php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Models\AppNotification; // tambahkan kalau pakai AppNotification
use Illuminate\Support\Facades\Auth; // tambahkan kalau pakai Auth

Route::get('/', function () {
    return view('welcome');
});

// Simple Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    
    // User Management
    Route::view('/users', 'users.index')->name('users.index');
    Route::view('/users/create', 'users.create')->name('users.create');
    Route::get('/users/{user}/edit', function ($userId) {
        return view('users.edit', ['userId' => $userId]);
    })->name('users.edit');
    
    // CRS Routes
    Route::view('/flights/import', 'flights.import')->name('flights.import');
    Route::view('/flights', 'flights.index')->name('flights.index');
    Route::view('/fuel-schedules/manager', 'fuel-schedules.manager')->name('fuel-schedules.manager');
    
    // Pengawas Routes
    Route::view('/reports/shift', 'reports.shift')->name('reports.shift');
    Route::view('/monitoring', 'fuel-schedules.monitoring')->name('monitoring.index');
    
    // CRO Routes
    Route::view('/my-schedules', 'fuel-schedules.my-schedules')->name('my-schedules');

    // Notifications
    Route::get('/notifications', function () {
        $notifications = AppNotification::with(['fuelSchedule.flight'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    })->name('notifications.index');
}); // ← ini yang kurang di kode kamu

Route::get('/home', [HomeController::class, 'index'])->name('home');
