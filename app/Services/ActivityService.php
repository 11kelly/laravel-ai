<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ActivityService
{
    /**
     * Get paginated activities with filters.
     */
    public function getPaginatedActivities(array $filters = [], bool $onlyPublished = false): LengthAwarePaginator
    {
        $query = Activity::query();

        // For frontend, only show published activities
        // When onlyPublished is true, always filter by published status
        if ($onlyPublished) {
            $query->where('status', 'published');
        } elseif (isset($filters['status'])) {
            // Only apply status filter when onlyPublished is false
            $query->where('status', $filters['status']);
        }

        // Filter by temporal status (only for published activities)
        // If temporal_status is provided, only show published activities with that temporal status
        if (isset($filters['temporal_status'])) {
            $query->where('status', 'published')
                ->temporalStatus($filters['temporal_status']);
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('start_time', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('end_time', '<=', $filters['date_to']);
        }

        // Order by start_time
        $query->orderBy('start_time', 'asc');

        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $query->paginate($perPage);
    }

    /**
     * Get a single activity by ID.
     */
    public function getActivityById(int $id, bool $onlyPublished = false): ?Activity
    {
        $query = Activity::query();
        
        if ($onlyPublished) {
            $query->where('status', 'published');
        }
        
        return $query->find($id);
    }

    /**
     * Get published activities.
     */
    public function getPublishedActivities(int $limit = 10): Collection
    {
        return Activity::published()
            ->orderBy('start_time', 'asc')
            ->limit($limit)
            ->get();
    }
}

