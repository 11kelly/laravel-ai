<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventPageController extends Controller
{
    /**
     * Display list of events
     */
    public function index(Request $request): View
    {
        $query = Event::with(['category', 'images'])
            ->published()
            ->upcoming();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('start_time', 'asc')->paginate(12);
        $categories = EventCategory::active()->ordered()->get();

        return view('events.index', compact('events', 'categories'));
    }

    /**
     * Display event details
     */
    public function show(string $slug): View
    {
        $event = Event::with(['category', 'images'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Check if user has booked this event
        $userBooking = null;
        if (auth()->check()) {
            $userBooking = $event->bookings()
                ->where('user_id', auth()->id())
                ->where('status', 'confirmed')
                ->first();
        }

        return view('events.show', compact('event', 'userBooking'));
    }
}

