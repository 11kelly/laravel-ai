<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::query()
            ->publiclyVisible()
            ->where('starts_at', '>', now())
            ->orderBy('starts_at');

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', '%' . $q . '%')
                    ->orWhere('summary', 'like', '%' . $q . '%');
            });
        }

        $from = $request->query('from');
        if ($from) {
            $query->where('starts_at', '>=', $from);
        }

        $to = $request->query('to');
        if ($to) {
            $query->where('starts_at', '<=', $to);
        }

        $activities = $query->paginate(12)->withQueryString();

        return view('activities.index', [
            'activities' => $activities,
            'filters' => [
                'q' => $q,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function show(Activity $activity): View
    {
        abort_unless($activity->isPubliclyVisible(), 404);

        $activity->load(['images']);

        return view('activities.show', [
            'activity' => $activity,
        ]);
    }
}


