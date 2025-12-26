<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\BookingLimitExceededException;
use App\Exceptions\EventNotAvailableException;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
        // Middleware is defined in routes/web.php
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $booking = $this->bookingService->createBooking(
                eventId: (int) $validated['event_id'],
                userId: auth()->id(),
                notes: $validated['notes'] ?? null
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'id' => $booking->id,
                    'event_id' => $booking->event_id,
                    'user_id' => $booking->user_id,
                    'status' => $booking->status,
                    'notes' => $booking->notes,
                    'created_at' => $booking->created_at->toISOString(),
                ], 201);
            }

            return redirect()
                ->route('profile.bookings')
                ->with('success', '预约成功！');
                
        } catch (EventNotAvailableException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
            
        } catch (BookingLimitExceededException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 409);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'event_id' => $validated['event_id'],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '预约失败，请稍后重试',
                ], 500);
            }

            return back()->withErrors(['error' => '预约失败，请稍后重试']);
        }
    }

    /**
     * Cancel the specified booking.
     */
    public function destroy(string $id, Request $request): JsonResponse|RedirectResponse
    {
        try {
            $this->bookingService->cancelBooking(
                bookingId: (int) $id,
                userId: auth()->id()
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '预约已取消',
                ]);
            }

            return back()->with('success', '预约已取消');
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], $e->getMessage() === '无权取消此预约' ? 403 : 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
