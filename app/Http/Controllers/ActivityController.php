<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {
    }

    /**
     * Display a listing of activities.
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'temporal_status' => $request->input('temporal_status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'per_page' => $request->input('per_page', 15),
        ];

        // Frontend should only show published activities by default
        $activities = $this->activityService->getPaginatedActivities($filters, onlyPublished: true);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $activities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'start_time' => $activity->start_time->toIso8601String(),
                        'end_time' => $activity->end_time->toIso8601String(),
                        'location' => $activity->location,
                        'max_participants' => $activity->max_participants,
                        'current_participants' => $activity->current_participants,
                        'image_url' => $activity->image_url,
                        'status' => $activity->status,
                        'temporal_status' => $activity->temporal_status,
                    ];
                }),
                'meta' => [
                    'current_page' => $activities->currentPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                    'last_page' => $activities->lastPage(),
                ],
            ]);
        }

        return view('activities.index', [
            'activities' => $activities,
        ]);
    }

    /**
     * Display the specified activity.
     */
    public function show(Request $request, int $id): View|JsonResponse
    {
        $activity = $this->activityService->getActivityById($id);

        if (! $activity) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Activity not found'], 404);
            }

            abort(404);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'short_description' => $activity->short_description,
                'start_time' => $activity->start_time->toIso8601String(),
                'end_time' => $activity->end_time->toIso8601String(),
                'location' => $activity->location,
                'max_participants' => $activity->max_participants,
                'current_participants' => $activity->current_participants,
                'image_url' => $activity->image_url,
                'status' => $activity->status,
                'temporal_status' => $activity->temporal_status,
                'is_available_for_booking' => $activity->isAvailableForBooking(),
            ]);
        }

        return view('activities.show', [
            'activity' => $activity,
        ]);
    }
}

