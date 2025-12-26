<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 验证预订数据完整性
 * 
 * 可配置为定时任务运行
 * 运行: php artisan bookings:verify-integrity
 */
final class VerifyBookingIntegrity extends Command
{
    protected $signature = 'bookings:verify-integrity {--auto-fix : 自动修复发现的问题}';

    protected $description = '验证预订数据完整性并可选自动修复';

    public function handle(): int
    {
        $this->info('🔍 验证预订数据完整性...');
        $this->newLine();

        $auto_fix = $this->option('auto-fix');
        $issues = [];

        // 检查 1: booked_count 与实际预订不一致
        $events = Event::with(['bookings' => function ($query) {
            $query->whereIn('status', BookingStatus::activeStatuses());
        }])->get();

        foreach ($events as $event) {
            $actual_count = $event->bookings->sum('participants_count');
            
            if ($actual_count !== $event->booked_count) {
                $issues[] = [
                    'type' => 'count_mismatch',
                    'event_id' => $event->id,
                    'stored' => $event->booked_count,
                    'actual' => $actual_count,
                ];

                if ($auto_fix) {
                    $event->update(['booked_count' => $actual_count]);
                    $this->info("✅ 修复活动 {$event->id}: {$event->booked_count} -> {$actual_count}");
                }
            }

            // 检查 2: booked_count 超过 capacity
            if ($event->booked_count > $event->capacity) {
                $issues[] = [
                    'type' => 'over_capacity',
                    'event_id' => $event->id,
                    'booked' => $event->booked_count,
                    'capacity' => $event->capacity,
                ];
                
                $this->warn("⚠️  活动 {$event->id} 预订数 ({$event->booked_count}) 超过容量 ({$event->capacity})");
            }
        }

        if (empty($issues)) {
            $this->info('✅ 所有数据完整性检查通过！');
            return self::SUCCESS;
        }

        // 记录到日志
        Log::info('Booking integrity check completed', [
            'total_issues' => count($issues),
            'auto_fixed' => $auto_fix,
            'issues' => $issues,
        ]);

        $this->newLine();
        $this->warn("发现 " . count($issues) . " 个问题");
        
        if (!$auto_fix) {
            $this->info("💡 运行 php artisan bookings:verify-integrity --auto-fix 自动修复");
        }

        return self::SUCCESS;
    }
}

