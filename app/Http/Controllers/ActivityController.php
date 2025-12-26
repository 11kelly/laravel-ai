<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ActivityService;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct(
        protected ActivityService $activityService,
        protected BookingService $bookingService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
        ];

        $activities = $this->activityService->getPublishedActivities($filters, 12);

        return view('activities.index', [
            'activities' => $activities,
            'filters' => $filters,
        ]);
    }

    public function show(string $slug): View
    {
        $activity = $this->activityService->findBySlug($slug);

        if (!$activity) {
            abort(404);
        }

        $hasBooked = false;
        $userBooking = null;

        if (auth()->check()) {
            $hasBooked = $this->bookingService->hasUserBooked(auth()->user(), $activity);
            if ($hasBooked) {
                $userBooking = $this->bookingService->getUserBookingForActivity(auth()->user(), $activity);
            }
        }

        return view('activities.show', [
            'activity' => $activity,
            'hasBooked' => $hasBooked,
            'userBooking' => $userBooking,
        ]);
    }
}

