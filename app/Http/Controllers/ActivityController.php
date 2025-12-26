<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Activity\ActivityNotReservableException;
use App\Exceptions\Activity\CapacityExceededException;
use App\Exceptions\Activity\DuplicateReservationException;
use App\Models\Activity;
use App\Services\ActivityService;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly ReservationService $reservationService,
    ) {}

    /**
     * Display activity listing.
     */
    public function index(): View
    {
        $activities = $this->activityService->getPublishedActivities(12);

        return view('activities.index', [
            'activities' => $activities,
        ]);
    }

    /**
     * Display activity details.
     */
    public function show(Activity $activity): View
    {
        // Only show published activities
        if ($activity->status !== 'published') {
            abort(404);
        }

        $hasReserved = false;
        if (Auth::check()) {
            $hasReserved = $this->reservationService->hasUserReserved(Auth::user(), $activity);
        }

        return view('activities.show', [
            'activity' => $activity,
            'hasReserved' => $hasReserved,
            'remainingCapacity' => $this->activityService->getRemainingCapacity($activity),
        ]);
    }

    /**
     * Create a reservation for the activity.
     */
    public function reserve(Request $request, Activity $activity): RedirectResponse
    {
        $request->validate([
            'remark' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->reservationService->createReservation(
                Auth::user(),
                $activity,
                ['remark' => $request->input('remark')],
            );

            return redirect()
                ->route('my.reservations')
                ->with('success', '預約成功！');
        } catch (ActivityNotReservableException|CapacityExceededException|DuplicateReservationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

