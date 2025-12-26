<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityService
{
    /**
     * Get published activities with pagination.
     *
     * @return LengthAwarePaginator<Activity>
     */
    public function getPublishedActivities(int $perPage = 15): LengthAwarePaginator
    {
        return Activity::query()
            ->published()
            ->notEnded()
            ->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get featured/upcoming activities for homepage.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Activity>
     */
    public function getFeaturedActivities(int $limit = 6): \Illuminate\Database\Eloquent\Collection
    {
        return Activity::query()
            ->published()
            ->upcoming()
            ->orderBy('start_time', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activity by ID.
     */
    public function getActivityById(int $activityId): Activity
    {
        return Activity::findOrFail($activityId);
    }

    /**
     * Check if activity is reservable.
     */
    public function isReservable(Activity $activity): bool
    {
        return $activity->isReservable();
    }

    /**
     * Get remaining capacity of activity.
     */
    public function getRemainingCapacity(Activity $activity): int
    {
        return $activity->getRemainingCapacity();
    }
}

