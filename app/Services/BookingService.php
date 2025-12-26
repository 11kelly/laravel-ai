<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BookingServiceInterface;
use App\DTOs\BookingEligibility;
use App\DTOs\BookingFilter;
use App\DTOs\CreateBookingRequest;
use App\Models\User;
use App\DTOs\PaginatedResponse;
use App\Exceptions\ActivityExpiredException;
use App\Exceptions\ActivityFullException;
use App\Exceptions\ActivityNotFoundException;
use App\Exceptions\DuplicateBookingException;
use App\Models\Activity;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingService implements BookingServiceInterface
{
    public function createBooking(CreateBookingRequest $request): Booking
    {
        $activity = Activity::find($request->activityId);

        if (!$activity) {
            throw new ActivityNotFoundException($request->activityId);
        }

        // 检查活动是否已过期
        if ($activity->end_date->isPast()) {
            throw new ActivityExpiredException($request->activityId);
        }

        // 检查活动是否发布
        if (!$activity->isPublished()) {
            throw new \InvalidArgumentException('Activity is not available for booking');
        }

        // 检查是否已有预约
        if (Booking::where('activity_id', $request->activityId)
            ->where('user_id', $request->userId)
            ->where('status', '!=', 'cancelled')
            ->exists()) {
            throw new DuplicateBookingException($request->activityId, $request->userId);
        }

        return DB::transaction(function () use ($request, $activity) {
            // 使用行锁检查和扣减名额
            $activity = Activity::where('id', $request->activityId)
                ->lockForUpdate()
                ->first();

            if (!$activity || !$activity->hasAvailableSlots()) {
                throw new ActivityFullException($request->activityId);
            }

            // 扣减名额
            $activity->decrement('available_slots');

            // 创建预约
            $booking = new Booking([
                'activity_id' => $request->activityId,
                'user_id' => $request->userId,
                'contact_info' => $request->contactInfo,
                'special_requirements' => $request->specialRequirements,
                'status' => 'confirmed',
            ]);

            $booking->save();

            return $booking->load('activity', 'user');
        });
    }

    /**
     * 从用户资料快速预约活动
     */
    public function quickBookFromProfile(int $activityId, int $userId, ?string $specialRequirements = null): Booking
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        if (!$user->hasCompleteProfile()) {
            throw new \InvalidArgumentException('请先完善您的个人资料');
        }

        $contactInfo = $user->getBookingContactInfo();

        $request = new CreateBookingRequest(
            activityId: $activityId,
            userId: $userId,
            contactInfo: $contactInfo,
            specialRequirements: $specialRequirements
        );

        return $this->createBooking($request);
    }

    public function cancelBooking(int $bookingId, int $userId): void
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $userId)
            ->first();

        if (!$booking) {
            throw new \InvalidArgumentException('Booking not found');
        }

        if (!$booking->canBeCancelled()) {
            throw new \InvalidArgumentException('Booking cannot be cancelled');
        }

        DB::transaction(function () use ($booking) {
            // 恢复名额
            $booking->activity->increment('available_slots');

            // 取消预约
            $booking->update(['status' => 'cancelled']);
        });
    }

    public function getUserBookings(int $userId, BookingFilter $filter): Collection
    {
        $query = Booking::where('user_id', $userId)
            ->with('activity')
            ->orderBy('created_at', 'desc');

        if ($filter->status !== null) {
            $query->where('status', $filter->status);
        }

        if ($filter->startDate !== null) {
            $query->where('created_at', '>=', $filter->startDate);
        }

        if ($filter->endDate !== null) {
            $query->where('created_at', '<=', $filter->endDate);
        }

        return $query->get();
    }

    public function getActivityBookings(int $activityId, BookingFilter $filter): PaginatedResponse
    {
        $query = Booking::where('activity_id', $activityId)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($filter->status !== null) {
            $query->where('status', $filter->status);
        }

        if ($filter->startDate !== null) {
            $query->where('created_at', '>=', $filter->startDate);
        }

        if ($filter->endDate !== null) {
            $query->where('created_at', '<=', $filter->endDate);
        }

        $paginated = $query->paginate(20);

        return new PaginatedResponse(
            $paginated->items(),
            $paginated->currentPage(),
            $paginated->perPage(),
            $paginated->total(),
            $paginated->lastPage()
        );
    }

    public function checkBookingEligibility(int $activityId, int $userId): BookingEligibility
    {
        $activity = Activity::find($activityId);

        if (!$activity) {
            return BookingEligibility::ineligible('Activity not found');
        }

        if (!$activity->isPublished()) {
            return BookingEligibility::ineligible('Activity is not available for booking');
        }

        if ($activity->end_date->isPast()) {
            return BookingEligibility::ineligible('Activity has expired');
        }

        if (!$activity->hasAvailableSlots()) {
            return BookingEligibility::ineligible('Activity is full');
        }

        if (Booking::where('activity_id', $activityId)
            ->where('user_id', $userId)
            ->where('status', '!=', 'cancelled')
            ->exists()) {
            return BookingEligibility::ineligible('Already booked for this activity');
        }

        return BookingEligibility::eligible();
    }
}
