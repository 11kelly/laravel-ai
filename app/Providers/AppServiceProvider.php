<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\AdminUserProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 注册自定义的 Admin User Provider
        // 用于后台认证，只允许 role='admin' 的用户登录
        Auth::provider('admin_eloquent', function ($app, array $config) {
            return new AdminUserProvider(
                $app['hash'],
                $config['model']
            );
        });
    }
}
