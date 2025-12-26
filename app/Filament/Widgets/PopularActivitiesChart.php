<?php

namespace App\Filament\Widgets;

use App\Contracts\StatisticsServiceInterface;
use App\DTOs\DateRange;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class PopularActivitiesChart extends ChartWidget
{
    protected ?string $heading = '热门活动排行榜';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $statisticsService = app(StatisticsServiceInterface::class);

        $dateRange = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        $popularActivities = $statisticsService->getPopularActivities($dateRange, 10);

        return [
            'datasets' => [
                [
                    'label' => '预约数量',
                    'data' => $popularActivities->pluck('bookingCount')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(6, 182, 212, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                        'rgb(6, 182, 212)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(168, 85, 247)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $popularActivities->map(function ($activity) {
                // 截断过长的活动标题并清理UTF-8字符
                $title = $activity->activityTitle;

                // 更安全的UTF-8清理：只移除真正的控制字符，保留UTF-8多字节序列
                $title = $this->sanitizeForJavaScript($title);

                // UTF-8安全的截断
                return $this->truncateUtf8String($title, 17);
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * 清理字符串以确保JavaScript兼容性
     */
    private function sanitizeForJavaScript(string $string): string
    {
        // 首先确保是有效的UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', 'auto');
        }

        // 移除真正的控制字符，但保留UTF-8多字节序列
        $cleaned = '';
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            $char = $string[$i];
            $ord = ord($char);

            // 跳过真正的控制字符
            if ($ord >= 0x00 && $ord <= 0x1F || $ord === 0x7F) {
                continue;
            }

            // 对于0x80-0x9F范围，检查是否是UTF-8多字节序列的开始
            if ($ord >= 0x80 && $ord <= 0x9F) {
                // 如果是UTF-8多字节序列的开始或中间字节，保留它
                if (($ord & 0xC0) === 0xC0 || ($ord & 0xC0) === 0x80) {
                    $cleaned .= $char;
                }
                // 否则跳过（真正的C1控制字符）
            } else {
                $cleaned .= $char;
            }
        }

        return $cleaned;
    }

    /**
     * UTF-8安全的字符串截断
     */
    private function truncateUtf8String(string $string, int $maxLength): string
    {
        if (mb_strlen($string, 'UTF-8') <= $maxLength) {
            return $string;
        }

        // 使用UTF-8安全的截断
        $truncated = mb_substr($string, 0, $maxLength, 'UTF-8');

        // 确保截断后的字符串仍然是有效的UTF-8
        if (!mb_check_encoding($truncated, 'UTF-8')) {
            // 如果截断破坏了UTF-8序列，尝试截断更少字符
            $truncated = mb_substr($string, 0, $maxLength - 1, 'UTF-8');
        }

        return $truncated . '...';
    }
}
