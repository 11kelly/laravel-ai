<?php

namespace App\Filament\Resources\Users\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $completedProfiles = User::where('profile_completed', true)->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('总用户数', $totalUsers)
                ->description('注册用户总数')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('资料完善', $completedProfiles)
                ->description($totalUsers > 0 ? number_format(($completedProfiles / $totalUsers) * 100, 1) . '% 已完善' : '暂无用户')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($completedProfiles > 0 ? 'success' : 'warning'),

            Stat::make('本周注册', $recentUsers)
                ->description('最近7天新注册用户')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($recentUsers > 0 ? 'info' : 'gray'),

            Stat::make('活跃用户', $totalUsers - User::whereDoesntHave('bookings')->count())
                ->description('有预约记录的用户')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
