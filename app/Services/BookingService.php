<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BookingLimitExceededException;
use App\Exceptions\EventNotAvailableException;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Create a new booking for an event.
     *
     * @throws EventNotAvailableException
     * @throws BookingLimitExceededException
     */
    public function createBooking(int $eventId, int $userId, ?string $notes = null): Booking
    {
        return DB::transaction(function () use ($eventId, $userId, $notes) {
            // Lock the event row for update to prevent race conditions
            $event = Event::where('id', $eventId)
                ->lockForUpdate()
                ->first();

            if (!$event) {
                throw new EventNotAvailableException('活动不存在');
            }

            // Validate event is available for booking
            if ($event->status !== 'published') {
                throw new EventNotAvailableException('活动未发布');
            }

            if (now()->isAfter($event->start_time)) {
                throw new EventNotAvailableException('活动已开始');
            }

            if ($event->booking_deadline && now()->isAfter($event->booking_deadline)) {
                throw new EventNotAvailableException('预约已截止');
            }

            if ($event->current_participants >= $event->max_participants) {
                throw new BookingLimitExceededException('活动名额已满');
            }

            // Check if user already booked this event
            $existingBooking = Booking::where('event_id', $eventId)
                ->where('user_id', $userId)
                ->first();

            if ($existingBooking) {
                throw new EventNotAvailableException('您已预约此活动');
            }

            // Create booking
            $booking = Booking::create([
                'event_id' => $eventId,
                'user_id' => $userId,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            // Increment current participants count
            $event->increment('current_participants');

            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'event_id' => $eventId,
                'user_id' => $userId,
            ]);

            return $booking;
        });
    }

    /**
     * Cancel a booking.
     *
     * @throws \Exception
     */
    public function cancelBooking(int $bookingId, int $userId): void
    {
        DB::transaction(function () use ($bookingId, $userId) {
            $booking = Booking::with('event')
                ->where('id', $bookingId)
                ->lockForUpdate()
                ->first();

            if (!$booking) {
                throw new \Exception('预约不存在');
            }

            if ($booking->user_id !== $userId) {
                throw new \Exception('无权取消此预约');
            }

            if ($booking->status === 'cancelled') {
                // Idempotent - already cancelled
                return;
            }

            if (now()->isAfter($booking->event->start_time)) {
                throw new \Exception('活动已开始，无法取消');
            }

            // Update booking status
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Decrement current participants count
            $booking->event->decrement('current_participants');

            Log::info('Booking cancelled', [
                'booking_id' => $bookingId,
                'user_id' => $userId,
            ]);
        });
    }
}

