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
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a new booking.
     *
     * @throws \Exception
     */
    public function createBooking(User $user, Event $event, ?string $notes = null): Booking
    {
        return DB::transaction(function () use ($user, $event, $notes) {
            // Lock the event record to prevent race conditions
            $event = Event::where('id', $event->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if user already has a booking for this event (with lock to prevent race conditions)
            // Note: Database unique constraint (user_id, event_id) also prevents duplicates
            $existingBooking = Booking::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->lockForUpdate()
                ->first();

            if ($existingBooking) {
                throw new \Exception('您已经预约过此活动');
            }

            // Check if event is published
            if (! $event->is_published) {
                throw new \Exception('此活动尚未发布');
            }

            // Check if event has available slots (re-check after lock)
            if ($event->isFull()) {
                throw new \Exception('活动名额已满');
            }

            // Check if event has started
            if ($event->start_time <= now()) {
                throw new \Exception('活动已开始，无法预约');
            }

            // Create booking
            $booking = Booking::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'status' => Booking::STATUS_PENDING,
                'notes' => $notes,
            ]);

            // Increment booked_count atomically
            $event->increment('booked_count');

            return $booking->fresh(['user', 'event']);
        });
    }

    /**
     * Cancel a booking.
     *
     * @throws \Exception
     */
    public function cancelBooking(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking) {
            // Lock the booking record to prevent race conditions
            $booking = Booking::where('id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if booking is already cancelled (re-check after lock)
            if ($booking->isCancelled()) {
                throw new \Exception('预约已取消');
            }

            // Lock the event record to prevent race conditions
            $event = Event::where('id', $booking->event_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if event has started
            if ($event->start_time <= now()) {
                throw new \Exception('活动已开始，无法取消预约');
            }

            // Update booking status
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Decrement booked_count atomically (only if booking was not cancelled)
            $event->decrement('booked_count');

            return true;
        });
    }

    /**
     * Confirm a booking.
     *
     * @throws \Exception
     */
    public function confirmBooking(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking) {
            // Lock the booking record to prevent race conditions
            $booking = Booking::where('id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if booking is already cancelled (re-check after lock)
            if ($booking->isCancelled()) {
                throw new \Exception('无法确认已取消的预约');
            }

            // Update booking status
            $booking->update([
                'status' => Booking::STATUS_CONFIRMED,
            ]);

            return true;
        });
    }
}

