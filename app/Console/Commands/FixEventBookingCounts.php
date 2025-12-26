<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 修复活动预订计数不一致问题
 * 
 * 运行: php artisan bookings:fix-counts
 */
final class FixEventBookingCounts extends Command
{
    protected $signature = 'bookings:fix-counts {--dry-run : 只显示不一致的数据，不修复}';

    protected $description = '修复活动预订计数（booked_count）与实际预订记录不一致的问题';

    public function handle(): int
    {
        $this->info('🔍 开始检查活动预订计数...');
        $this->newLine();

        $is_dry_run = $this->option('dry-run');
        $fixed_count = 0;
        $inconsistent_events = [];

        $events = Event::with(['bookings' => function ($query) {
            $query->whereIn('status', BookingStatus::activeStatuses());
        }])->get();

        foreach ($events as $event) {
            $actual_count = $event->bookings->sum('participants_count');
            $stored_count = $event->booked_count;

            if ($actual_count !== $stored_count) {
                $inconsistent_events[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'stored' => $stored_count,
                    'actual' => $actual_count,
                    'diff' => $actual_count - $stored_count,
                ];

                if (!$is_dry_run) {
                    $event->update(['booked_count' => $actual_count]);
                    $fixed_count++;
                }
            }
        }

        if (empty($inconsistent_events)) {
            $this->info('✅ 所有活动的预订计数都是准确的！');
            return self::SUCCESS;
        }

        $this->table(
            ['活动 ID', '标题', '数据库值', '实际值', '差异'],
            array_map(fn($e) => [
                $e['id'],
                substr($e['title'], 0, 30) . '...',
                $e['stored'],
                $e['actual'],
                $e['diff'] > 0 ? "+{$e['diff']}" : $e['diff'],
            ], $inconsistent_events)
        );

        $this->newLine();

        if ($is_dry_run) {
            $this->warn("⚠️  发现 " . count($inconsistent_events) . " 个不一致的活动");
            $this->info("💡 运行 php artisan bookings:fix-counts 进行修复");
        } else {
            $this->info("✅ 已修复 {$fixed_count} 个活动的预订计数");
        }

        return self::SUCCESS;
    }
}

