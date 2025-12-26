<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class EventService
{
    /**
     * Get published events with pagination
     */
    public function getPublishedEvents(
        ?string $status = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Event::published()
            ->withCount(['bookings as booked_count' => function ($q) {
                $q->whereIn('status', ['pending', 'confirmed']);
            }]);

        // Apply status filter
        if ($status === 'ongoing') {
            $query->ongoing();
        } elseif ($status === 'ended') {
            $query->ended();
        } elseif ($status === 'upcoming') {
            $query->upcoming();
        } else {
            // Default: show upcoming (not started) and ongoing events
            // This ensures published events are visible even if start time is in the past
            $query->where(function ($q) {
                $q->upcoming()->orWhere(function ($q2) {
                    $q2->ongoing();
                });
            });
        }

        if ($startDate) {
            $query->where('start_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('end_time', '<=', $endDate);
        }

        return $query->orderBy('start_time', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get event by ID
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getEventById(int $id): Event
    {
        return Event::withCount(['bookings as booked_count' => function ($q) {
            $q->whereIn('status', ['pending', 'confirmed']);
        }])->findOrFail($id);
    }

    /**
     * Store uploaded image
     */
    public function storeImage($file): string
    {
        $path = $file->store('events', 'public');
        return $path;
    }

    /**
     * Delete image
     */
    public function deleteImage(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}
