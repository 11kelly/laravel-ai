<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\BookingEligibility;
use App\DTOs\BookingFilter;
use App\DTOs\CreateBookingRequest;
use App\DTOs\PaginatedResponse;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingServiceInterface
{
    /**
     * 创建预约
     */
    public function createBooking(CreateBookingRequest $request): Booking;

    /**
     * 取消预约
     */
    public function cancelBooking(int $bookingId, int $userId): void;

    /**
     * 获取用户预约列表
     */
    public function getUserBookings(int $userId, BookingFilter $filter): Collection;

    /**
     * 获取活动预约列表（管理员）
     */
    public function getActivityBookings(int $activityId, BookingFilter $filter): PaginatedResponse;

    /**
     * 验证预约资格
     */
    public function checkBookingEligibility(int $activityId, int $userId): BookingEligibility;
}
