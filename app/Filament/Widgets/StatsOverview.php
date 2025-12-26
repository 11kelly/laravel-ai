<?php

namespace App\Filament\Widgets;

use App\Contracts\StatisticsServiceInterface;
use App\DTOs\DateRange;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $statisticsService = app(StatisticsServiceInterface::class);

        // 获取最近30天的统计数据
        $dateRange = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        $globalStats = $statisticsService->getGlobalStats($dateRange);

        // 获取活动统计
        $totalActivities = \App\Models\Activity::count();
        $publishedActivities = \App\Models\Activity::where('status', 'published')->count();
        $draftActivities = \App\Models\Activity::where('status', 'draft')->count();

        // 获取预约统计
        $totalBookings = \App\Models\Booking::count();
        $confirmedBookings = \App\Models\Booking::where('status', 'confirmed')->count();
        $pendingBookings = \App\Models\Booking::where('status', 'pending')->count();

        return [
            Stat::make('总活动数', $totalActivities)
                ->description('系统中所有活动')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('已发布活动', $publishedActivities)
                ->description("草稿: {$draftActivities}")
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make('总预约数', $totalBookings)
                ->description("已确认: {$confirmedBookings}")
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('待处理预约', $pendingBookings)
                ->description('需要确认的预约')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingBookings > 0 ? 'warning' : 'gray'),

            Stat::make('本月收入', 'NT$ ' . number_format($globalStats->totalRevenue, 0))
                ->description('最近30天总收入')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('平均预约率', $totalActivities > 0 ? round(($totalBookings / $totalActivities), 1) . ' 人/活动' : '0 人/活动')
                ->description('每个活动的平均预约人数')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('gray'),
        ];
    }
}
