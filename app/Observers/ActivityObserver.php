<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Observers;

use App\Models\Activity;

class ActivityObserver
{
    /**
     * Handle the Activity "updated" event.
     */
    public function updated(Activity $activity): void
    {
        if (!$activity->wasChanged('status')) {
            return;
        }

        $newStatus = $activity->status;
        $oldStatus = $activity->getOriginal('status');

        // 处理不同的状态变更
        switch ($newStatus) {
            case 'cancelled':
                // 活动被取消，取消所有活跃预约
                if ($oldStatus !== 'cancelled') {
                    $this->cancelActivityBookings($activity, '活动已取消，预约已取消');
                }
                break;

            case 'completed':
                // 活动完成，处理预约状态
                if ($oldStatus !== 'completed') {
                    $this->completeActivityBookings($activity);
                }
                break;

            case 'published':
                // 活动重新发布，可以选择恢复被取消的预约
                // 注意：这里不自动恢复预约，因为：
                // 1. 用户可能已经因为活动取消而改变了计划
                // 2. 活动重新发布可能有新的条件
                // 如果需要恢复预约，需要管理员手动处理或提供专门的恢复功能
                break;
        }
    }

    /**
     * 取消活动的所有预约
     */
    private function cancelActivityBookings(Activity $activity, string $reason = '活动已取消'): void
    {
        // 获取当前待确认和已确认的预约数量
        $activeBookingsCount = $activity->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        // 将所有待确认和已确认的预约状态改为已取消
        $activity->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);

        // 恢复名额（只恢复刚刚被取消的预约数量）
        if ($activeBookingsCount > 0) {
            $activity->increment('available_slots', $activeBookingsCount);
        }
    }

    /**
     * 完成活动的预约处理
     */
    private function completeActivityBookings(Activity $activity): void
    {
        // 将所有已确认的预约状态改为已参加
        $activity->bookings()
            ->where('status', 'confirmed')
            ->update([
                'status' => 'attended',
                'updated_at' => now()
            ]);

        // 将待确认的预约改为已取消（活动已经结束了）
        $pendingCount = $activity->bookings()
            ->where('status', 'pending')
            ->count();

        $activity->bookings()
            ->where('status', 'pending')
            ->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);

        // 恢复被取消预约的名额
        if ($pendingCount > 0) {
            $activity->increment('available_slots', $pendingCount);
        }
    }
}
