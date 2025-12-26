<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ActivityManagementServiceInterface;
use App\DTOs\ActivityFilter;
use App\DTOs\CreateActivityRequest;
use App\DTOs\PaginatedResponse;
use App\DTOs\UpdateActivityRequest;
use App\Exceptions\ActivityNotFoundException;
use App\Exceptions\DuplicateTitleException;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ActivityManagementService implements ActivityManagementServiceInterface
{
    public function createActivity(CreateActivityRequest $request, ?int $userId = null): Activity
    {
        // 检查标题是否重复
        if (Activity::where('title', $request->title)->exists()) {
            throw new DuplicateTitleException($request->title);
        }

        $activity = new Activity([
            'title' => $request->title,
            'slug' => $this->generateUniqueSlug($request->title),
            'description' => $request->description,
            'price' => $request->price ?? 0,
            'capacity' => $request->capacity,
            'available_slots' => $request->capacity,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
            'status' => 'draft',
            'created_by' => $userId ?? auth()->id(),
        ]);

        $activity->save();

        return $activity;
    }

    public function updateActivity(int $activityId, UpdateActivityRequest $request): Activity
    {
        $activity = Activity::find($activityId);

        if (!$activity) {
            throw new ActivityNotFoundException($activityId);
        }

        $updates = [];

        if ($request->title !== null) {
            // 检查标题是否重复（排除当前活动）
            if (Activity::where('title', $request->title)->where('id', '!=', $activityId)->exists()) {
                throw new DuplicateTitleException($request->title);
            }
            $updates['title'] = $request->title;
            $updates['slug'] = $this->generateUniqueSlug($request->title, $activityId);
        }

        if ($request->description !== null) {
            $updates['description'] = $request->description;
        }

        if ($request->price !== null) {
            $updates['price'] = $request->price;
        }

        if ($request->capacity !== null) {
            // 如果容量增加，需要调整可用名额
            $currentCapacity = $activity->capacity;
            $newCapacity = $request->capacity;

            if ($newCapacity > $currentCapacity) {
                $updates['available_slots'] = $activity->available_slots + ($newCapacity - $currentCapacity);
            } elseif ($newCapacity < $currentCapacity) {
                // 如果容量减少，确保可用名额不为负数
                $usedSlots = $currentCapacity - $activity->available_slots;
                if ($newCapacity < $usedSlots) {
                    throw new \InvalidArgumentException('Cannot reduce capacity below current bookings');
                }
                $updates['available_slots'] = $newCapacity - $usedSlots;
            }

            $updates['capacity'] = $newCapacity;
        }

        if ($request->startDate !== null) {
            $updates['start_date'] = $request->startDate;
        }

        if ($request->endDate !== null) {
            $updates['end_date'] = $request->endDate;
        }

        if ($request->status !== null) {
            $updates['status'] = $request->status;
        }

        $activity->update($updates);

        return $activity->fresh();
    }

    public function deleteActivity(int $activityId): void
    {
        $activity = Activity::find($activityId);

        if (!$activity) {
            throw new ActivityNotFoundException($activityId);
        }

        // 检查是否有预约记录
        if ($activity->bookings()->exists()) {
            throw new \InvalidArgumentException('Cannot delete activity with existing bookings');
        }

        $activity->delete();
    }

    public function getActivities(ActivityFilter $filter): PaginatedResponse
    {
        $query = Activity::query()->with('creator');

        if ($filter->status !== null) {
            $query->where('status', $filter->status);
        }

        if ($filter->startDate !== null) {
            $query->where('start_date', '>=', $filter->startDate);
        }

        if ($filter->endDate !== null) {
            $query->where('end_date', '<=', $filter->endDate);
        }

        if ($filter->search !== null) {
            $query->where(function (Builder $q) use ($filter) {
                $q->where('title', 'like', '%' . $filter->search . '%')
                  ->orWhere('description', 'like', '%' . $filter->search . '%');
            });
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

    public function getActivity(int $activityId): Activity
    {
        $activity = Activity::with('creator')->find($activityId);

        if (!$activity) {
            throw new ActivityNotFoundException($activityId);
        }

        return $activity;
    }

    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Activity::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
