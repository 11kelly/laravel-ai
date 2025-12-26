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
        $query = Event::published()->with('bookings');

        // Filter by status
        $status = $request->get('status', 'upcoming');
        
        switch ($status) {
            case 'upcoming':
                $query->upcoming()->orderBy('start_time', 'asc');
                break;
            case 'ongoing':
                $query->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->orderBy('start_time', 'asc');
                break;
            case 'ended':
                $query->where('end_time', '<', now())
                    ->orderBy('start_time', 'desc');
                break;
        }

        $events = $query->paginate(15);

        return view('events.index', compact('events', 'status'));
    }

    /**
     * Display the specified event.
     */
    public function show(string $id): View
    {
        $event = Event::with('bookings')->findOrFail($id);

        // Check if event is published or user is admin
        if ($event->status !== 'published') {
            abort(403, '活动未发布');
        }

        $userBooking = null;
        if (auth()->check()) {
            $userBooking = $event->bookings()
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('events.show', compact('event', 'userBooking'));
    }
}
