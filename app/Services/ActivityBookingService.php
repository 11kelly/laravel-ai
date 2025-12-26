<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ActivityManagementServiceInterface;
use App\Contracts\BookingServiceInterface;
use App\Contracts\StatisticsServiceInterface;
use App\DTOs\ActivityFilter;
use App\DTOs\BookingEligibility;
use App\DTOs\CreateActivityRequest;
use App\DTOs\CreateBookingRequest;
use App\DTOs\DateRange;
use App\DTOs\PaginatedResponse;
use App\DTOs\UpdateActivityRequest;
use App\Exceptions\ActivityNotFoundException;
use App\Exceptions\DuplicateBookingException;
use App\Models\Activity;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * 活动预约服务示例
 * 演示如何使用各个服务接口
 */
class ActivityBookingService
{
    public function __construct(
        private ActivityManagementServiceInterface $activityService,
        private BookingServiceInterface $bookingService,
        private StatisticsServiceInterface $statisticsService
    ) {}

    /**
     * 创建新活动并返回
     */
    public function createNewActivity(): Activity
    {
        $request = new CreateActivityRequest(
            title: 'Laravel 开发工作坊',
            description: '学习 Laravel 框架的最佳实践',
            price: 99.00,
            capacity: 50,
            startDate: Carbon::now()->addDays(7),
            endDate: Carbon::now()->addDays(8)
        );

        return $this->activityService->createActivity($request);
    }

    /**
     * 发布活动
     */
    public function publishActivity(int $activityId): Activity
    {
        $activity = $this->activityService->getActivity($activityId);

        if ($activity->status === 'draft') {
            $updateRequest = new UpdateActivityRequest(
                status: 'published'
            );

            return $this->activityService->updateActivity($activityId, $updateRequest);
        }

        return $activity;
    }

    /**
     * 获取发布活动的分页列表
     */
    public function getPublishedActivities(int $page = 1): PaginatedResponse
    {
        $filter = new ActivityFilter(
            status: 'published'
        );

        return $this->activityService->getActivities($filter);
    }

    /**
     * 创建预约
     */
    public function createBooking(int $activityId, int $userId): Booking
    {
        $request = new CreateBookingRequest(
            activityId: $activityId,
            userId: $userId,
            contactInfo: [
                'name' => '张三',
                'email' => 'zhangsan@example.com',
                'phone' => '13800138000'
            ],
            specialRequirements: '需要无障碍设施'
        );

        return $this->bookingService->createBooking($request);
    }

    /**
     * 检查预约资格
     */
    public function checkBookingEligibility(int $activityId, int $userId): BookingEligibility
    {
        return $this->bookingService->checkBookingEligibility($activityId, $userId);
    }

    /**
     * 获取用户预约列表
     */
    public function getUserBookings(int $userId): Collection
    {
        return $this->bookingService->getUserBookings($userId, new App\DTOs\BookingFilter());
    }

    /**
     * 取消预约
     */
    public function cancelUserBooking(int $bookingId, int $userId): void
    {
        $this->bookingService->cancelBooking($bookingId, $userId);
    }

    /**
     * 获取活动统计
     */
    public function getActivityStats(int $activityId): App\DTOs\ActivityStats
    {
        return $this->statisticsService->getActivityStats($activityId);
    }

    /**
     * 获取全局统计
     */
    public function getGlobalStats(): App\DTOs\GlobalStats
    {
        $range = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        return $this->statisticsService->getGlobalStats($range);
    }

    /**
     * 获取热门活动排行
     */
    public function getPopularActivities(): Collection
    {
        $range = new DateRange(
            startDate: Carbon::now()->subDays(30),
            endDate: Carbon::now()
        );

        return $this->statisticsService->getPopularActivities($range, 10);
    }

    /**
     * 完整的预约流程示例
     */
    public function completeBookingFlow(int $userId): array
    {
        try {
            // 1. 创建活动
            $activity = $this->createNewActivity();

            // 2. 发布活动
            $publishedActivity = $this->publishActivity($activity->id);

            // 3. 检查预约资格
            $eligibility = $this->checkBookingEligibility($publishedActivity->id, $userId);

            if (!$eligibility->isEligible) {
                throw new \Exception($eligibility->reason);
            }

            // 4. 创建预约
            $booking = $this->createBooking($publishedActivity->id, $userId);

            // 5. 获取统计数据
            $stats = $this->getActivityStats($publishedActivity->id);

            return [
                'success' => true,
                'activity' => $publishedActivity,
                'booking' => $booking,
                'stats' => $stats
            ];

        } catch (ActivityNotFoundException $e) {
            return ['success' => false, 'error' => 'Activity not found'];
        } catch (DuplicateBookingException $e) {
            return ['success' => false, 'error' => 'Already booked for this activity'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
