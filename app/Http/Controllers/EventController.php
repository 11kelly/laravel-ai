<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $eventService
    ) {
    }

    /**
     * Display a listing of events
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $perPage = min((int) $request->query('per_page', 15), 50);

        $events = $this->eventService->getPublishedEvents(
            $status,
            $startDate,
            $endDate,
            $perPage
        );

        return view('events.index', [
            'events' => $events,
        ]);
    }

    /**
     * Display the specified event
     */
    public function show(int $id): View
    {
        $event = $this->eventService->getEventById($id);

        if ($event->status !== 'published') {
            abort(404);
        }

        return view('events.show', [
            'event' => $event,
        ]);
    }
}
