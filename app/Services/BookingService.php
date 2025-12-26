<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace App\Services;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class BookingService
{
    /**
     * @param User $user
     * @param Activity $activity
     * @param array $data
     * @return Booking
     * @throws Exception
     */
    public function createBooking(User $user, Activity $activity, array $data): Booking
    {
        return DB::transaction(function () use ($user, $activity, $data) {
            // 原子扣减库存并检查
            $affected = DB::table('activities')
                ->where('id', $activity->id)
                ->where('remaining_spots', '>=', $data['ticket_quantity'] ?? 1)
                ->where('status', 'published')
                ->decrement('remaining_spots', $data['ticket_quantity'] ?? 1);

            if ($affected === 0) {
                throw new Exception('RESOURCE_EXHAUSTED');
            }

            // 创建预约记录
            return Booking::create([
                'user_id' => $user->id,
                'activity_id' => $activity->id,
                'status' => 'pending',
                'booking_code' => $this->generateBookingCode(),
                'participant_info' => $data['participant_info'],
                'ticket_quantity' => $data['ticket_quantity'] ?? 1,
            ]);
        });
    }

    /**
     * 取消预约
     *
     * @param Booking $booking
     * @return void
     * @throws Exception
     */
    public function cancelBooking(Booking $booking): void
    {
        if ($booking->status === 'cancelled') {
            throw new Exception('BOOKING_ALREADY_CANCELLED');
        }

        if ($booking->status === 'attended') {
            throw new Exception('CANNOT_CANCEL_ATTENDED_BOOKING');
        }

        DB::transaction(function () use ($booking) {
            // 更新预约状态
            $booking->update(['status' => 'cancelled']);

            // 恢复库存
            DB::table('activities')
                ->where('id', $booking->activity_id)
                ->increment('remaining_spots', $booking->ticket_quantity);
        });
    }

    protected function generateBookingCode(): string
    {
        return 'BK-' . strtoupper(Str::random(8));
    }
}

