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
use Illuminate\View\View;

class UserBookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
        // Middleware is applied in routes/web.php via Route::middleware('auth')
    }

    /**
     * Display a listing of the user's bookings.
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = [
            'status' => $request->input('status'),
            'per_page' => $request->input('per_page', 15),
        ];

        $bookings = $this->bookingService->getUserBookings(Auth::id(), $filters);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $bookings->items(),
                'meta' => [
                    'current_page' => $bookings->currentPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'last_page' => $bookings->lastPage(),
                ],
            ]);
        }

        return view('user.dashboard', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $booking = $this->bookingService->cancelBooking(
                $id,
                Auth::id(),
                $validated['reason'] ?? null
            );

            return response()->json([
                'id' => $booking->id,
                'status' => $booking->status,
                'cancelled_at' => $booking->cancelled_at?->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;

            return response()->json([
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}

