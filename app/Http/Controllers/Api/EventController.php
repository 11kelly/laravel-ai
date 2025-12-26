<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListEventsRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Get list of events
     */
    public function index(ListEventsRequest $request): JsonResponse
    {
        $query = Event::with(['category', 'images'])
            ->published();

        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            match ($status) {
                'upcoming' => $query->upcoming(),
                'ongoing' => $query->ongoing(),
                'past' => $query->past(),
                default => null,
            };
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Search
        if ($request->has('search') && $request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('start_time', 'asc')->paginate($request->getPerPage());

        return response()->json([
            'data' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'slug' => $event->slug,
                    'description' => $event->description,
                    'cover_image_url' => $event->cover_image_url,
                    'start_time' => $event->start_time->toIso8601String(),
                    'end_time' => $event->end_time->toIso8601String(),
                    'location' => $event->location,
                    'capacity' => $event->capacity,
                    'booked_count' => $event->booked_count,
                    'is_bookable' => $event->isBookable(),
                    'category' => $event->category ? [
                        'id' => $event->category->id,
                        'name' => $event->category->name,
                        'slug' => $event->category->slug,
                    ] : null,
                ];
            }),
            'meta' => [
                'current_page' => $events->currentPage(),
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'last_page' => $events->lastPage(),
            ],
        ]);
    }

    /**
     * Get event details
     */
    public function show(string $id): JsonResponse
    {
        // Try to find by ID first, then by slug
        $event = Event::with(['category', 'images'])
            ->published()
            ->where(function ($query) use ($id) {
                $query->where('id', $id)
                    ->orWhere('slug', $id);
            })
            ->first();

        if (!$event) {
            return response()->json([
                'message' => 'Event not found.',
            ], 404);
        }

        return response()->json([
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'description' => $event->description,
            'images' => $event->images->map(fn ($img) => [
                'url' => $img->full_url,
                'order' => $img->display_order,
            ]),
            'start_time' => $event->start_time->toIso8601String(),
            'end_time' => $event->end_time->toIso8601String(),
            'location' => $event->location,
            'capacity' => $event->capacity,
            'booked_count' => $event->booked_count,
            'booking_deadline' => $event->booking_deadline?->toIso8601String(),
            'is_bookable' => $event->isBookable(),
            'booking_rules' => $event->booking_rules,
            'organizer' => [
                'name' => $event->organizer_name,
                'contact' => $event->organizer_contact,
            ],
            'category' => $event->category ? [
                'id' => $event->category->id,
                'name' => $event->category->name,
                'slug' => $event->category->slug,
            ] : null,
        ]);
    }
}
