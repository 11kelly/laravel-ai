<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\ActivityFilter;
use App\DTOs\CreateActivityRequest;
use App\DTOs\PaginatedResponse;
use App\DTOs\UpdateActivityRequest;
use App\Models\Activity;

interface ActivityManagementServiceInterface
{
    /**
     * 创建活动
     */
    public function createActivity(CreateActivityRequest $request, ?int $userId = null): Activity;

    /**
     * 更新活动
     */
    public function updateActivity(int $activityId, UpdateActivityRequest $request): Activity;

    /**
     * 删除活动
     */
    public function deleteActivity(int $activityId): void;

    /**
     * 获取活动列表（分页）
     */
    public function getActivities(ActivityFilter $filter): PaginatedResponse;

    /**
     * 获取单个活动详情
     */
    public function getActivity(int $activityId): Activity;
}
