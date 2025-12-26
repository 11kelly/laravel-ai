<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取所有用户和活动
        $users = User::all();
        $events = Event::all();

        if ($users->isEmpty() || $events->isEmpty()) {
            $this->command->warn('请先运行 EventSeeder 和创建用户数据！');
            return;
        }

        // 为每个活动创建一些预约
        foreach ($events as $event) {
            // 随机选择 3-8 个用户为这个活动创建预约
            $bookingCount = min(rand(3, 8), $event->capacity, $users->count());
            $selectedUsers = $users->random($bookingCount);

            foreach ($selectedUsers as $user) {
                // 检查是否已经存在预约
                $existingBooking = Booking::where('user_id', $user->id)
                    ->where('event_id', $event->id)
                    ->first();

                if ($existingBooking) {
                    continue;
                }

                // 随机选择状态
                $statuses = [
                    Booking::STATUS_PENDING,
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_CONFIRMED, // 增加确认状态的概率
                ];
                $status = $statuses[array_rand($statuses)];

                // 创建预约
                Booking::create([
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'status' => $status,
                    'notes' => $this->generateNotes($status),
                    'cancelled_at' => $status === Booking::STATUS_CANCELLED ? now() : null,
                ]);
            }

            // 更新活动的已预约数量
            $confirmedCount = Booking::where('event_id', $event->id)
                ->where('status', Booking::STATUS_CONFIRMED)
                ->count();
            
            $event->update(['booked_count' => $confirmedCount]);
        }

        $this->command->info('预约数据生成完成！');
    }

    /**
     * 生成预约备注
     */
    private function generateNotes(string $status): ?string
    {
        $notes = [
            Booking::STATUS_PENDING => [
                '期待参加此次活动',
                '希望能学习到新知识',
                '已确认时间可以参加',
                null,
            ],
            Booking::STATUS_CONFIRMED => [
                '已确认参加，感谢！',
                '会准时到达',
                '期待与大家交流',
                null,
            ],
            Booking::STATUS_CANCELLED => [
                '因临时有事无法参加，抱歉',
                '时间冲突，已取消',
                '个人原因取消预约',
            ],
        ];

        $statusNotes = $notes[$status] ?? [null];
        return $statusNotes[array_rand($statusNotes)];
    }
}

