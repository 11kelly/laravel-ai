<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Storage 路由 - 提供对上传文件的访问，仅限图片文件
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    // 防止目录遍历攻击
    if (strpos($path, '..') !== false) {
        abort(403, 'Directory traversal not allowed');
    }

    if (!file_exists($fullPath) || !is_file($fullPath)) {
        abort(404);
    }

    // 只允许访问图片文件
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        abort(403, 'File type not allowed');
    }

    $mimeType = mime_content_type($fullPath);

    // 验证MIME类型是否为图片
    if (!str_starts_with($mimeType, 'image/')) {
        abort(403, 'Invalid file type');
    }

    return response()->file($fullPath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=86400', // 缓存24小时
    ]);
})->where('path', '.*');

// 认证路由
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Illuminate\Http\Request $request) {
    $credentials = $request->only('email', 'password');

    if (auth()->attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('activities.index'));
    }

    return back()->withErrors([
        'email' => '邮箱或密码错误',
    ]);
})->name('login.post');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:500',
    ]);

    $user = \App\Models\User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        'contact_info' => [
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
        ],
        'profile_completed' => true,
    ]);

    auth()->login($user);

    return redirect()->route('dashboard.index')->with('success', '注册成功！欢迎加入我们。');
})->name('register.post');

Route::post('/logout', function (Illuminate\Http\Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// 前台活动页面
Route::get('/activities', function () {
    return view('activities.index');
})->name('activities.index');

Route::get('/activities/{id}', function ($id) {
    return view('activities.show', ['activityId' => $id]);
})->name('activities.show');

// 法律页面（隐私政策、服务条款）
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/privacy', function () {
        return view('legal.privacy');
    })->name('privacy');

    Route::get('/terms', function () {
        return view('legal.terms');
    })->name('terms');
});

// 用户后台（需要登录）
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::delete('/bookings/{bookingId}/cancel', [DashboardController::class, 'cancelBooking'])->name('booking.cancel');
    Route::get('/stats', [DashboardController::class, 'stats'])->name('stats');

    // 个人资料管理
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
});

// API 路由（用于前端 AJAX 请求）
Route::prefix('api')->group(function () {
    Route::get('/activities', [ActivityController::class, 'index'])->name('api.activities.index');
    Route::get('/activities/{id}', [ActivityController::class, 'show'])->name('api.activities.show');
    Route::post('/activities/{activityId}/book', [ActivityController::class, 'book'])->middleware('auth')->name('api.activities.book');
    Route::get('/stats', [ActivityController::class, 'stats'])->name('api.stats');
    Route::get('/demo', [ActivityController::class, 'demo'])->middleware('auth')->name('api.demo');

    // 用户相关API
    Route::get('/user/profile', [DashboardController::class, 'getProfile'])->middleware('auth')->name('api.user.profile');
});
