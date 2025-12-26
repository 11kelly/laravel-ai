<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\ActivityRanking;
use App\DTOs\ActivityStats;
use App\DTOs\DateRange;
use App\DTOs\GlobalStats;
use Illuminate\Support\Collection;

interface StatisticsServiceInterface
{
    /**
     * 获取活动统计概览
     */
    public function getActivityStats(int $activityId): ActivityStats;

    /**
     * 获取全局统计数据
     */
    public function getGlobalStats(DateRange $range): GlobalStats;

    /**
     * 获取热门活动排行
     */
    public function getPopularActivities(DateRange $range, int $limit): Collection;
}
