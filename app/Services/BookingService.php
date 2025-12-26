<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function getUserBookings(User $user, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Booking::query()
            ->forUser($user->id)
            ->with(['activity.creator'])
            ->orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
            match ($status) {
                'upcoming' => $query->confirmed()->whereHas('activity', fn ($q) => $q->where('start_time', '>', now())),
                'completed' => $query->completed(),
                'cancelled' => $query->cancelled(),
                default => null,
            };
        }

        return $query->paginate($perPage);
    }

    public function createBooking(User $user, Activity $activity, int $participants = 1, ?string $remarks = null): array
    {
        if ($this->hasUserBooked($user, $activity)) {
            return [
                'success' => false,
                'message' => '您已预约过此活动',
                'code' => 409,
            ];
        }

        $validationResult = $this->validateBooking($activity, $participants);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        try {
            return DB::transaction(function () use ($user, $activity, $participants, $remarks): array {
                $activity = Activity::query()->lockForUpdate()->find($activity->id);

                if ($activity->booked_count + $participants > $activity->capacity) {
                    return [
                        'success' => false,
                        'message' => '名额不足',
                        'code' => 409,
                    ];
                }

                $booking = Booking::create([
                    'user_id' => $user->id,
                    'activity_id' => $activity->id,
                    'participants' => $participants,
                    'remarks' => $remarks,
                    'status' => 'confirmed',
                ]);

                $this->activityService->incrementBookedCount($activity, $participants);

                Log::info('Booking created', [
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'activity_id' => $activity->id,
                    'participants' => $participants,
                ]);

                return [
                    'success' => true,
                    'message' => '预约成功',
                    'booking' => $booking,
                ];
            });
        } catch (\Throwable $e) {
            Log::error('Booking creation failed', [
                'user_id' => $user->id,
                'activity_id' => $activity->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => '预约失败，请稍后重试',
                'code' => 500,
            ];
        }
    }

    public function cancelBooking(Booking $booking, ?string $reason = null): array
    {
        if ($booking->status !== 'confirmed') {
            return [
                'success' => false,
                'message' => '此预约无法取消',
                'code' => 409,
            ];
        }

        if ($booking->activity->start_time <= now()) {
            return [
                'success' => false,
                'message' => '活动已开始，无法取消',
                'code' => 409,
            ];
        }

        try {
            return DB::transaction(function () use ($booking, $reason): array {
                // 使用悲观锁防止并发取消导致的竞态条件
                $booking = Booking::query()->lockForUpdate()->find($booking->id);

                // 双重检查状态（防止并发请求）
                if ($booking->status !== 'confirmed') {
                    return [
                        'success' => false,
                        'message' => '此预约无法取消',
                        'code' => 409,
                    ];
                }

                $booking->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $reason,
                ]);

                $this->activityService->decrementBookedCount($booking->activity, $booking->participants);

                Log::info('Booking cancelled', [
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'activity_id' => $booking->activity_id,
                    'reason' => $reason,
                ]);

                return [
                    'success' => true,
                    'message' => '预约已取消',
                ];
            });
        } catch (\Throwable $e) {
            Log::error('Booking cancellation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => '取消失败，请稍后重试',
                'code' => 500,
            ];
        }
    }

    public function hasUserBooked(User $user, Activity $activity): bool
    {
        return Booking::query()
            ->where('user_id', $user->id)
            ->where('activity_id', $activity->id)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }

    public function getUserBookingForActivity(User $user, Activity $activity): ?Booking
    {
        return Booking::query()
            ->where('user_id', $user->id)
            ->where('activity_id', $activity->id)
            ->where('status', '!=', 'cancelled')
            ->first();
    }

    protected function validateBooking(Activity $activity, int $participants): array
    {
        if ($activity->status !== 'published') {
            return [
                'success' => false,
                'message' => '活动未发布',
                'code' => 409,
            ];
        }

        if ($activity->end_time < now()) {
            return [
                'success' => false,
                'message' => '活动已结束',
                'code' => 409,
            ];
        }

        if ($activity->registration_deadline && $activity->registration_deadline < now()) {
            return [
                'success' => false,
                'message' => '报名已截止',
                'code' => 409,
            ];
        }

        if ($activity->booked_count + $participants > $activity->capacity) {
            return [
                'success' => false,
                'message' => '名额不足',
                'code' => 409,
            ];
        }

        return ['success' => true];
    }
}

