<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    // 注册接口限流：3次/小时/IP
    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:3,60');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    // 登录接口限流：5次/分钟/IP
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

