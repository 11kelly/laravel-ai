<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserBookingController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

// Home redirect to activities
Route::get('/', function () {
    return redirect()->route('activities.index');
});

// Public routes
Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{id}', [ActivityController::class, 'show'])->name('activities.show');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Booking routes (require auth)
Route::middleware('auth')->group(function () {
    Route::post('/activities/{activity_id}/bookings', [BookingController::class, 'store'])->name('bookings.store');
});

// User routes (require auth)
Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserBookingController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [UserBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{id}/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [UserProfileController::class, 'update'])->name('profile.update');
});
