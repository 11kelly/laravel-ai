<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateBookingRequest;
use App\Http\Requests\Api\ListBookingsRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingCancellationService;
use App\Services\EventBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(
        private readonly EventBookingService $booking_service,
        private readonly BookingCancellationService $cancellation_service
    ) {}

    /**
     * Create a booking
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $event = Event::findOrFail($request->event_id);
            $user = $request->user();

            if (!$this->booking_service->checkRecentBookingAttempts($user, $event)) {
                return response()->json([
                    'message' => 'Please wait a few seconds before trying again.',
                ], 429);
            }

            $booking = $this->booking_service->createBooking(
                $event,
                $user,
                $request->participants_count,
                $request->notes
            );

            return response()->json([
                'id' => $booking->id,
                'event_id' => $booking->event_id,
                'user_id' => $booking->user_id,
                'status' => $booking->status->value,
                'participants_count' => $booking->participants_count,
                'booked_at' => $booking->created_at->toIso8601String(),
            ], 201);
        } catch (\Exception $e) {
            return $this->handleBookingError($e, $request->event_id, $request->user()->id);
        }
    }

    /**
     * Get user's bookings
     */
    public function index(ListBookingsRequest $request): JsonResponse
    {
        $query = Booking::with('event.category')
            ->where('user_id', $request->user()->id);

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $bookings = $query->orderBy('created_at', 'desc')
            ->paginate($request->getPerPage());

        return BookingResource::collection($bookings)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Cancel a booking
     */
    public function destroy(string $id): JsonResponse
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        $user = request()->user();

        if ($booking->user_id !== $user->id) {
            return response()->json([
                'message' => 'You do not have permission to cancel this booking.',
            ], 403);
        }

        try {
            $this->cancellation_service->cancelBooking($booking, $user);

            return response()->json([
                'message' => 'Booking cancelled successfully.',
                'refunded_slots' => $booking->participants_count,
            ]);
        } catch (\Exception $e) {
            return $this->handleCancellationError($e, $id, $user->id);
        }
    }

    /**
     * Handle booking creation error
     */
    private function handleBookingError(\Exception $e, int $event_id, int $user_id): JsonResponse
    {
        Log::warning('Booking creation failed', [
            'event_id' => $event_id,
            'user_id' => $user_id,
            'error' => $e->getMessage(),
        ]);

        $status_code = match (true) {
            str_contains($e->getMessage(), 'already booked') => 403,
            str_contains($e->getMessage(), 'no longer bookable') => 409,
            str_contains($e->getMessage(), 'Not enough capacity') => 409,
            str_contains($e->getMessage(), 'deadline has passed') => 422,
            str_contains($e->getMessage(), 'banned') => 403,
            default => 500,
        };

        return response()->json(['message' => $e->getMessage()], $status_code);
    }

    /**
     * Handle booking cancellation error
     */
    private function handleCancellationError(\Exception $e, string $booking_id, int $user_id): JsonResponse
    {
            Log::warning('Booking cancellation failed', [
            'booking_id' => $booking_id,
            'user_id' => $user_id,
                'error' => $e->getMessage(),
            ]);

        $status_code = match (true) {
                str_contains($e->getMessage(), 'permission') => 403,
                str_contains($e->getMessage(), 'already been cancelled') => 409,
                str_contains($e->getMessage(), 'already started') => 409,
                default => 500,
            };

        return response()->json(['message' => $e->getMessage()], $status_code);
    }
}
