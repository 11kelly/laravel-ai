<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Activity\ActivityNotReservableException;
use App\Exceptions\Activity\CapacityExceededException;
use App\Exceptions\Activity\DuplicateReservationException;
use App\Exceptions\Activity\ReservationNotCancellableException;
use App\Models\Activity;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationService
{
    /**
     * Create a reservation for user.
     *
     * @param array{remark?: string|null} $data
     *
     * @throws ActivityNotReservableException
     * @throws DuplicateReservationException
     * @throws CapacityExceededException
     */
    public function createReservation(User $user, Activity $activity, array $data = []): Reservation
    {
        // Pre-check: activity status
        if ($activity->status !== 'published') {
            throw new ActivityNotReservableException('此活動尚未開放預約');
        }

        if ($activity->hasStarted()) {
            throw new ActivityNotReservableException('此活動已開始，無法預約');
        }

        // Pre-check: duplicate reservation
        if ($this->hasUserReserved($user, $activity)) {
            throw new DuplicateReservationException();
        }

        try {
            return DB::transaction(function () use ($user, $activity, $data) {
                // Lock the activity row for update
                $lockedActivity = Activity::query()
                    ->where('id', $activity->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedActivity) {
                    throw new ActivityNotReservableException('活動不存在');
                }

                // Check capacity again within transaction
                if ($lockedActivity->capacity > 0 && $lockedActivity->reserved_count >= $lockedActivity->capacity) {
                    throw new CapacityExceededException();
                }

                // Create reservation
                $reservation = Reservation::create([
                    'user_id' => $user->id,
                    'activity_id' => $activity->id,
                    'status' => 'confirmed',
                    'remark' => $data['remark'] ?? null,
                    'reserved_at' => now(),
                ]);

                // Increment reserved count
                $lockedActivity->increment('reserved_count');

                // Log audit event
                Log::channel('daily')->info('Reservation created', [
                    'user_id' => $user->id,
                    'activity_id' => $activity->id,
                    'reservation_id' => $reservation->id,
                ]);

                return $reservation;
            });
        } catch (UniqueConstraintViolationException) {
            throw new DuplicateReservationException();
        }
    }

    /**
     * Cancel a reservation.
     *
     * @throws ReservationNotCancellableException
     */
    public function cancelReservation(Reservation $reservation): Reservation
    {
        if (! $reservation->canBeCancelled()) {
            if ($reservation->status === 'cancelled') {
                return $reservation; // Idempotent
            }
            throw new ReservationNotCancellableException();
        }

        return DB::transaction(function () use ($reservation) {
            // Lock reservation
            $lockedReservation = Reservation::query()
                ->where('id', $reservation->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedReservation || $lockedReservation->status === 'cancelled') {
                return $lockedReservation ?? $reservation;
            }

            // Update reservation status
            $lockedReservation->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Decrement reserved count (only if > 0 to prevent underflow)
            Activity::query()
                ->where('id', $lockedReservation->activity_id)
                ->where('reserved_count', '>', 0)
                ->decrement('reserved_count');

            // Log audit event
            Log::channel('daily')->info('Reservation cancelled', [
                'user_id' => $lockedReservation->user_id,
                'reservation_id' => $lockedReservation->id,
                'activity_id' => $lockedReservation->activity_id,
            ]);

            return $lockedReservation->fresh();
        });
    }

    /**
     * Get user's reservations with pagination.
     *
     * @return LengthAwarePaginator<Reservation>
     */
    public function getUserReservations(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Reservation::query()
            ->where('user_id', $user->id)
            ->with('activity')
            ->orderByDesc('reserved_at')
            ->paginate($perPage);
    }

    /**
     * Check if user has reserved an activity.
     */
    public function hasUserReserved(User $user, Activity $activity): bool
    {
        return Reservation::query()
            ->where('user_id', $user->id)
            ->where('activity_id', $activity->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }
}

