<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventBookingService
{
    /**
     * Create a new booking for an event
     *
     * @throws \Exception
     */
    public function createBooking(Event $event, User $user, int $participantsCount, ?string $notes = null): Booking
    {
        // Check if user is banned
        if ($user->is_banned) {
            throw new \Exception('User is banned and cannot create bookings.');
        }

        // Validate participants count
        if ($participantsCount < 1) {
            throw new \Exception('Participants count must be at least 1.');
        }

        return DB::transaction(function () use ($event, $user, $participantsCount, $notes) {
            // Check for duplicate booking
            $existingBooking = Booking::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->where('status', BookingStatus::CONFIRMED)
                ->first();

            if ($existingBooking) {
                throw new \Exception('You have already booked this event.');
            }

            // Lock event row for update
            $event = Event::where('id', $event->id)
                ->lockForUpdate()
                ->first();

            // Verify event is bookable
            if (!$event->isBookable()) {
                throw new \Exception('This event is no longer bookable.');
            }

            // Check remaining capacity
            $remainingCapacity = $event->getRemainingCapacity();
            if ($remainingCapacity < $participantsCount) {
                throw new \Exception("Not enough capacity. Only {$remainingCapacity} spots remaining.");
            }

            // Check booking deadline
            if ($event->booking_deadline && $event->booking_deadline->isPast()) {
                throw new \Exception('Booking deadline has passed.');
            }

            // Create booking
            $booking = Booking::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'participants_count' => $participantsCount,
                'status' => BookingStatus::CONFIRMED,
                'notes' => $notes,
            ]);

            // Update booked count (防护性检查避免超过容量)
            $new_count = $event->booked_count + $participantsCount;
            if ($new_count > $event->capacity) {
                Log::warning('Booking count would exceed capacity', [
                    'event_id' => $event->id,
                    'current_count' => $event->booked_count,
                    'adding' => $participantsCount,
                    'capacity' => $event->capacity,
                ]);
            }
            $event->increment('booked_count', $participantsCount);

            // Log the booking
            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'event_id' => $event->id,
                'user_id' => $user->id,
                'participants_count' => $participantsCount,
            ]);

            return $booking->load('event', 'user');
        });
    }

    /**
     * Check if user has reached booking limit for recent requests (anti-spam)
     */
    public function checkRecentBookingAttempts(User $user, Event $event): bool
    {
        $recentAttempt = Booking::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('created_at', '>', now()->subSeconds(5))
            ->exists();

        return !$recentAttempt;
    }
}

