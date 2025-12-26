<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');

Route::middleware(['auth'])->group(function () {
    Route::post('/activities/{activity}/book', [ActivityController::class, 'book'])->name('activities.book');
    Route::get('/bookings', [ActivityController::class, 'myBookings'])->name('bookings.index');
    Route::post('/bookings/{booking}/cancel', [ActivityController::class, 'cancelBooking'])->name('bookings.cancel');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Auth Routes (Assuming Laravel Fortify or Breeze is used, but if not, we can add basic ones or the user will provide)
// For now, assume the user will handle standard Auth routes.
require __DIR__.'/auth.php';
