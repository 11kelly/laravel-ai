<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 首页
Route::get('/', [HomeController::class, 'index'])->name('home');

// 活动列表和详情（公开）
Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{activity:slug}', [ActivityController::class, 'show'])->name('activities.show');

// 认证路由
require __DIR__.'/auth.php';

// 需要登录的路由
Route::middleware('auth')->group(function () {
    // 预约（添加速率限制：10次/分钟/用户）
    Route::post('/bookings', [BookingController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('bookings.store');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])
        ->middleware('throttle:10,1')
        ->name('bookings.destroy');

    // 用户中心
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
        Route::get('/bookings', [UserController::class, 'bookings'])->name('bookings');
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::get('/password', [UserController::class, 'password'])->name('password');
        Route::put('/password', [UserController::class, 'updatePassword'])->name('password.update');
    });
});
