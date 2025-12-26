<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Create a booking for an activity.
     *
     * @throws \Exception
     */
    public function createBooking(int $activityId, int $userId, ?string $notes = null): Booking
    {
        return DB::transaction(function () use ($activityId, $userId, $notes) {
            // Lock the activity row to prevent concurrent booking
            $activity = Activity::lockForUpdate()->findOrFail($activityId);

            // Validate activity is available for booking
            if (! $activity->isAvailableForBooking()) {
                Log::warning('Booking attempt failed: activity not available', [
                    'activity_id' => $activityId,
                    'user_id' => $userId,
                    'activity_status' => $activity->status,
                    'current_participants' => $activity->current_participants,
                    'max_participants' => $activity->max_participants,
                ]);

                throw new \Exception('Activity is not available for booking', 409);
            }

            // Check if user already has a booking for this activity
            $existingBooking = Booking::where('activity_id', $activityId)
                ->where('user_id', $userId)
                ->first();

            if ($existingBooking) {
                Log::info('Booking attempt failed: duplicate booking', [
                    'activity_id' => $activityId,
                    'user_id' => $userId,
                    'existing_booking_id' => $existingBooking->id,
                ]);

                throw new \Exception('You have already booked this activity', 409);
            }

            // Create the booking
            $booking = Booking::create([
                'activity_id' => $activityId,
                'user_id' => $userId,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            // Update activity participant count (only if max_participants > 0)
            if ($activity->max_participants > 0) {
                $activity->increment('current_participants');
            }

            Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'activity_id' => $activityId,
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
    public function cancelBooking(int $bookingId, int $userId, ?string $reason = null): Booking
    {
        return DB::transaction(function () use ($bookingId, $userId, $reason) {
            $booking = Booking::where('id', $bookingId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $booking->canBeCancelled()) {
                Log::warning('Booking cancellation failed: not allowed', [
                    'booking_id' => $bookingId,
                    'user_id' => $userId,
                    'booking_status' => $booking->status,
                ]);

                throw new \Exception('This booking cannot be cancelled', 409);
            }

            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
            ]);

            // Decrease activity participant count (only if max_participants > 0)
            $activity = $booking->activity;
            if ($activity && $activity->max_participants > 0) {
                $activity->decrement('current_participants');
            } elseif (! $activity) {
                // Activity may have been soft deleted, log warning
                Log::warning('Booking cancelled but activity not found', [
                    'booking_id' => $bookingId,
                    'activity_id' => $booking->activity_id,
                    'user_id' => $userId,
                ]);
            }

            Log::info('Booking cancelled successfully', [
                'booking_id' => $bookingId,
                'activity_id' => $booking->activity_id,
                'user_id' => $userId,
            ]);

            return $booking->fresh();
        });
    }

    /**
     * Get user's bookings with pagination.
     */
    public function getUserBookings(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = Booking::where('user_id', $userId)
            ->with(['activity'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $query->paginate($perPage);
    }

    /**
     * Get all bookings with filters (for admin).
     */
    public function getAllBookings(array $filters = []): LengthAwarePaginator
    {
        $query = Booking::with(['activity', 'user'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['activity_id'])) {
            $query->where('activity_id', $filters['activity_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $query->paginate($perPage);
    }
}

