<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Welcome page - redirect to events
// 使用 302 临时重定向，避免缓存问题
Route::get('/', function () {
    return redirect()->route('frontend.events.index', [], 302);
})->name('home');

// Dashboard - redirect based on user role
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect('/admin');
    }
    return redirect()->route('frontend.events.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Frontend Routes (for regular users)
// 注意：prefix 不应该包含前导斜杠，Laravel会自动处理
Route::prefix('events')->name('frontend.events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');
});

// 临时测试路由 - 用于调试404问题
Route::get('/test-events', function () {
    try {
        return response()->json([
            'status' => 'ok',
            'route' => 'test-events',
            'message' => '测试路由正常工作',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::middleware(['auth', 'frontend.user'])->group(function () {
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Booking Management (authenticated users only)
    Route::prefix('bookings')->name('frontend.bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::post('/events/{event}', [BookingController::class, 'store'])->name('store');
        Route::delete('/{booking}', [BookingController::class, 'cancel'])->name('cancel');
    });
});

// Language switching
Route::get('/locale/{locale}', [\App\Http\Controllers\LocaleController::class, 'switch'])
    ->name('locale.switch')
    ->where('locale', 'en|zh_CN');

require __DIR__.'/auth.php';
