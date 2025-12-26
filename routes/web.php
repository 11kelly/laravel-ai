<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// 首页重定向到活动列表
Route::get('/', function () {
    return redirect()->route('events.index');
});

// 活动相关路由
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// 认证路由
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 预约路由（需要登录）
Route::post('/events/{event}/book', [BookingController::class, 'store'])->name('bookings.store')->middleware('auth');
Route::delete('/bookings/{booking}/cancel', [BookingController::class, 'destroy'])->name('bookings.cancel')->middleware('auth');

// 个人中心路由（需要登录）
Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index')->middleware('auth');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::get('/profile/bookings', [ProfileController::class, 'bookings'])->name('profile.bookings')->middleware('auth');
