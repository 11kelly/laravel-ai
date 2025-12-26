<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Console\Command;

/**
 * 检查活动在前台的可见性
 * 
 * 运行: php artisan events:check-visibility
 */
final class CheckEventVisibility extends Command
{
    protected $signature = 'events:check-visibility {--id= : 检查指定活动 ID}';

    protected $description = '检查活动是否会在前台显示';

    public function handle(): int
    {
        $event_id = $this->option('id');

        if ($event_id) {
            $event = Event::find($event_id);
            if (!$event) {
                $this->error("活动 ID {$event_id} 不存在");
                return self::FAILURE;
            }
            $events = collect([$event]);
        } else {
            $events = Event::all();
        }

        $this->info('🔍 检查活动前台可见性...');
        $this->newLine();

        $visible_count = 0;
        $hidden_count = 0;

        foreach ($events as $event) {
            $is_published = $event->status === EventStatus::PUBLISHED;
            $not_ended = $event->end_time >= now();
            $should_show = $is_published && $not_ended;

            if ($should_show) {
                $visible_count++;
                $this->info("✅ {$event->title} (ID: {$event->id})");
            } else {
                $hidden_count++;
                $reasons = [];
                if (!$is_published) {
                    $reasons[] = "状态: {$event->status->label()}";
                }
                if ($event->end_time < now()) {
                    $reasons[] = "已结束 ({$event->end_time->format('Y-m-d H:i:s')})";
                }
                $this->warn("❌ {$event->title} (ID: {$event->id})");
                $this->line("   原因: " . implode(', ', $reasons));
            }
        }

        $this->newLine();
        $this->info("📊 统计:");
        $this->line("   可见: {$visible_count} 个");
        $this->line("   隐藏: {$hidden_count} 个");

        if ($hidden_count > 0) {
            $this->newLine();
            $this->comment("💡 提示:");
            $this->line("   - 活动状态必须设置为 '已发布'");
            $this->line("   - 活动结束时间必须 >= 当前时间");
        }

        return self::SUCCESS;
    }
}

