<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ActivityService
{
    public function getPublishedActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Activity::query()
            ->published()
            ->with('creator')
            ->orderBy('start_time', 'asc');

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function getFeaturedActivities(int $limit = 6): Collection
    {
        return Activity::query()
            ->published()
            ->featured()
            ->upcoming()
            ->with('creator')
            ->orderBy('start_time', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getUpcomingActivities(int $limit = 10): Collection
    {
        return Activity::query()
            ->published()
            ->upcoming()
            ->with('creator')
            ->orderBy('start_time', 'asc')
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug): ?Activity
    {
        return Activity::query()
            ->published()
            ->where('slug', $slug)
            ->with(['creator', 'bookings'])
            ->first();
    }

    public function findById(int $id): ?Activity
    {
        return Activity::query()
            ->with(['creator', 'bookings'])
            ->find($id);
    }

    public function incrementBookedCount(Activity $activity, int $participants = 1): bool
    {
        return $activity->increment('booked_count', $participants) > 0;
    }

    public function decrementBookedCount(Activity $activity, int $participants = 1): bool
    {
        if ($activity->booked_count >= $participants) {
            return $activity->decrement('booked_count', $participants) > 0;
        }

        return false;
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            match ($filters['status']) {
                'upcoming' => $query->upcoming(),
                'ongoing' => $query->ongoing(),
                'ended' => $query->ended(),
                default => null,
            };
        }

        if (!empty($filters['featured'])) {
            $query->featured();
        }
    }
}

