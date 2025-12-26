<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Me\BookingController as MeBookingController;
use App\Http\Controllers\Me\ProfileController as MeProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/activities');
Route::redirect('/dashboard', '/me/bookings')->middleware('auth');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
Route::post('/activities/{activity}/bookings', [BookingController::class, 'store'])
    ->middleware('auth')
    ->name('activities.bookings.store');

Route::middleware('auth')->group(function () {
    Route::get('/me/bookings', [MeBookingController::class, 'index'])->name('me.bookings.index');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/me/profile', [MeProfileController::class, 'edit'])->name('me.profile.edit');
    Route::post('/me/profile', [MeProfileController::class, 'update'])->name('me.profile.update');
});
