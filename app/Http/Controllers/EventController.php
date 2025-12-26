<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index(Request $request): View
    {
        $query = Event::query()->published()->upcoming()->orderBy('start_time', 'asc');

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $searchPattern = "%{$search}%";
            $query->where(function ($q) use ($searchPattern) {
                $q->where('title', 'like', $searchPattern)
                    ->orWhere('description', 'like', $searchPattern)
                    ->orWhere('location', 'like', $searchPattern);
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location', $request->get('location'));
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->get('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_time', '<=', $request->get('end_date'));
        }

        $events = $query->paginate(12)->withQueryString();
        $locations = Event::published()->distinct()->pluck('location');

        return view('events.index', [
            'events' => $events,
            'locations' => $locations,
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): View
    {
        if (! $event->is_published) {
            abort(404);
        }

        return view('events.show', [
            'event' => $event,
        ]);
    }
}

