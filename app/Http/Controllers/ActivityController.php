<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\ActivityManagementServiceInterface;
use App\Contracts\BookingServiceInterface;
use App\Contracts\StatisticsServiceInterface;
use App\DTOs\ActivityFilter;
use App\DTOs\CreateActivityRequest as CreateActivityDTO;
use App\DTOs\CreateBookingRequest;
use App\DTOs\DateRange;
use App\Http\Requests\CreateActivityRequest;
use App\Services\ActivityBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * 活动控制器示例
 * 演示如何在控制器中使用业务服务
 */
class ActivityController extends Controller
{
    public function __construct(
        private ActivityManagementServiceInterface $activityService,
        private BookingServiceInterface $bookingService,
        private StatisticsServiceInterface $statisticsService,
        private ActivityBookingService $activityBookingService
    ) {}

    /**
     * 获取活动列表（前台只显示已发布的活动）
     */
    public function index(Request $request): JsonResponse
    {
        $filter = new ActivityFilter(
            status: $request->query('status', 'published'), // 默认只显示已发布的活动
            search: $request->query('search')
        );

        $activities = $this->activityService->getActivities($filter);

        return response()->json($activities);
    }

    /**
     * 获取单个活动详情
     */
    public function show(int $id): JsonResponse
    {
        try {
            $activity = $this->activityService->getActivity($id);
            return response()->json($activity);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * 创建活动
     */
    public function store(CreateActivityRequest $request): JsonResponse
    {
        try {
            $userId = auth()->id();

            $activityRequest = new CreateActivityDTO(
                title: $request->validated('title'),
                description: $request->validated('description'),
                price: $request->validated('price'),
                capacity: $request->validated('capacity'),
                startDate: Carbon::parse($request->validated('start_date')),
                endDate: Carbon::parse($request->validated('end_date'))
            );

            $activity = $this->activityService->createActivity($activityRequest, $userId);

            return response()->json($activity, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * 创建预约（使用用户个人资料）
     */
    public function book(Request $request, int $activityId): JsonResponse
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['error' => '请先登录后再进行预约'], 401);
            }

            // 使用用户资料快速预约
            $booking = $this->bookingService->quickBookFromProfile(
                activityId: $activityId,
                userId: $userId,
                specialRequirements: $request->input('special_requirements')
            );

            return response()->json([
                'success' => true,
                'message' => '预约成功',
                'booking' => $booking
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * 获取统计数据
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            if ($request->has('activity_id')) {
                $stats = $this->statisticsService->getActivityStats($request->query('activity_id'));
            } else {
                $range = new DateRange(
                    startDate: Carbon::parse($request->query('start_date', Carbon::now()->subDays(30))),
                    endDate: Carbon::parse($request->query('end_date', Carbon::now()))
                );
                $stats = $this->statisticsService->getGlobalStats($range);
            }

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * 演示完整预约流程
     */
    public function demo(): JsonResponse
    {
        $result = $this->activityBookingService->completeBookingFlow(auth()->id());

        return response()->json($result);
    }
}
