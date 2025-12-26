<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use Illuminate\Support\Facades\Route;

// Public Event APIs with rate limiting (60 requests per minute)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
});

// Protected Booking APIs with stricter rate limiting
Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/my-bookings', [BookingController::class, 'index']);
    
    // Booking creation with very strict rate limiting (10 requests per minute)
    Route::post('/bookings', [BookingController::class, 'store'])
        ->middleware('throttle:10,1');
    
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
});

