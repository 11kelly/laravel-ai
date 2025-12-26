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
        private readonly ActivityService $activityService,
    ) {}

    public function __invoke(): View
    {
        $featuredActivities = $this->activityService->getFeaturedActivities(6);

        return view('home', [
            'featuredActivities' => $featuredActivities,
        ]);
    }
}

