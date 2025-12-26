<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Create a booking for an event
     *
     * @throws \Exception
     */
    public function createBooking(int $eventId, int $userId, ?string $notes = null): Booking
    {
        return DB::transaction(function () use ($eventId, $userId, $notes) {
            // Lock the event row to prevent concurrent booking conflicts
            $event = Event::lockForUpdate()->findOrFail($eventId);

            // Validate event status
            if ($event->status !== 'published') {
                throw new \Exception('活动未发布，无法预约', 400);
            }

            // Validate event time
            if ($event->start_time <= now()) {
                throw new \Exception('活动已开始，无法预约', 400);
            }

            // Check if user already booked this event
            $existingBooking = Booking::where('event_id', $eventId)
                ->where('user_id', $userId)
                ->first();

            if ($existingBooking) {
                throw new \Exception('您已预约该活动', 400);
            }

            // Check capacity
            $bookedCount = Booking::where('event_id', $eventId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            if ($bookedCount >= $event->capacity) {
                throw new \Exception('活动已满', 400);
            }

            // Create booking
            $booking = Booking::create([
                'event_id' => $eventId,
                'user_id' => $userId,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'user_id' => $userId,
                'event_id' => $eventId,
                'ip' => request()->ip(),
            ]);

            return $booking;
        });
    }

    /**
     * Cancel a booking
     *
     * @throws \Exception
     */
    public function cancelBooking(int $bookingId, int $userId): Booking
    {
        return DB::transaction(function () use ($bookingId, $userId) {
            $booking = Booking::findOrFail($bookingId);

            // Validate ownership
            if ($booking->user_id !== $userId) {
                throw new \Exception('无权操作此预约', 403);
            }

            // Validate status
            if ($booking->status === 'cancelled') {
                throw new \Exception('预约已取消', 400);
            }

            // Validate event time
            if ($booking->event->start_time <= now()) {
                throw new \Exception('活动已开始，无法取消', 400);
            }

            // Update booking
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
            ]);

            Log::info('Booking cancelled', [
                'booking_id' => $booking->id,
                'user_id' => $userId,
                'event_id' => $booking->event_id,
                'ip' => request()->ip(),
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Get user bookings
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserBookings(int $userId, ?string $status = null, int $perPage = 15)
    {
        $query = Booking::where('user_id', $userId)
            ->with(['event']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

