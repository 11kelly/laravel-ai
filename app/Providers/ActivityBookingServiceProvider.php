<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ActivityManagementServiceInterface;
use App\Contracts\BookingServiceInterface;
use App\Contracts\StatisticsServiceInterface;
use App\Services\ActivityManagementService;
use App\Services\BookingService;
use App\Services\StatisticsService;
use Illuminate\Support\ServiceProvider;

/**
 * 活动预约服务提供者
 * 注册所有业务服务到依赖注入容器
 */
class ActivityBookingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 注册活动管理服务
        $this->app->bind(ActivityManagementServiceInterface::class, ActivityManagementService::class);

        // 注册预约服务
        $this->app->bind(BookingServiceInterface::class, BookingService::class);

        // 注册统计服务
        $this->app->bind(StatisticsServiceInterface::class, StatisticsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 可以在这里添加路由、服务初始化等逻辑
    }
}
