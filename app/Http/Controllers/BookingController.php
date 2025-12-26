<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request, int $activityId): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 403);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $booking = $this->bookingService->createBooking(
                $activityId,
                Auth::id(),
                $validated['notes'] ?? null
            );

            return response()->json([
                'id' => $booking->id,
                'activity_id' => $booking->activity_id,
                'user_id' => $booking->user_id,
                'status' => $booking->status,
                'created_at' => $booking->created_at->toIso8601String(),
            ], 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;

            return response()->json([
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}

