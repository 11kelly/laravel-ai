<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

/**
 * Frontend Booking Service
 * 
 * 处理前端用户预订相关业务逻辑
 */
final readonly class FrontendBookingService
{
    /**
     * 创建预订申请（待审核状态）
     * 
     * @throws \Exception
     */
    public function createBooking(
        Event $event,
        int $user_id,
        int $participants_count,
        ?string $notes = null
    ): Booking {
        $this->validateBooking($event, $user_id, $participants_count);

        return DB::transaction(function () use ($event, $user_id, $participants_count, $notes) {
            return Booking::create([
                'event_id' => $event->id,
                'user_id' => $user_id,
                'participants_count' => $participants_count,
                'status' => BookingStatus::PENDING,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * 验证预订是否有效
     * 
     * @throws \Exception
     */
    private function validateBooking(Event $event, int $user_id, int $participants_count): void
    {
        if ($event->status !== EventStatus::PUBLISHED) {
            throw new \Exception('此活动无法预约');
        }

        if ($event->start_time->isPast()) {
            throw new \Exception('活动已开始，无法预约');
        }

        if ($this->hasActiveBooking($event->id, $user_id)) {
            throw new \Exception('您已预约此活动');
        }

        $this->checkCapacity($event, $participants_count);
    }

    /**
     * 检查用户是否已有活跃的预订
     */
    private function hasActiveBooking(int $event_id, int $user_id): bool
    {
        return Booking::where('event_id', $event_id)
            ->where('user_id', $user_id)
            ->whereIn('status', BookingStatus::activeStatuses())
            ->exists();
    }

    /**
     * 检查活动名额是否足够
     * 
     * @throws \Exception
     */
    private function checkCapacity(Event $event, int $participants_count): void
    {
        if (!$event->max_participants) {
            return;
        }

        $booked_count = Booking::where('event_id', $event->id)
            ->whereIn('status', BookingStatus::activeStatuses())
            ->sum('participants_count');

        $available = $event->max_participants - $booked_count;

        if ($participants_count > $available) {
            throw new \Exception("活动名额不足，仅剩 {$available} 个名额");
        }
    }
}

