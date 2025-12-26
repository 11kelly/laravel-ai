<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ActivityService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    public function index(): View
    {
        $featuredActivities = $this->activityService->getFeaturedActivities(6);
        $upcomingActivities = $this->activityService->getUpcomingActivities(8);

        return view('home', [
            'featuredActivities' => $featuredActivities,
            'upcomingActivities' => $upcomingActivities,
        ]);
    }
}

