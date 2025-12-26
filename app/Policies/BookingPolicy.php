<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own bookings via API
        // Admins can view all via Filament
        return true;
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // User can view their own booking
        return $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user): bool
    {
        // Non-banned users can create bookings
        return !$user->is_banned;
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Users cannot update bookings directly
        // Only cancel is allowed
        return false;
    }

    /**
     * Determine whether the user can delete/cancel the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // User can cancel their own confirmed booking
        return $user->id === $booking->user_id && $booking->canBeCancelled();
    }

    /**
     * Determine whether the user can cancel the booking.
     * Alias for delete with more semantic meaning.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $this->delete($user, $booking);
    }

    /**
     * Determine whether the user can restore the booking.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the booking.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }
}

