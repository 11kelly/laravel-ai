<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view published events
        return true;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(?User $user, Event $event): bool
    {
        // Published events are visible to everyone
        // Draft events only visible to creator or admins
        if ($event->status === 'published') {
            return true;
        }

        return $user !== null && $user->id === $event->created_by;
    }

    /**
     * Determine whether the user can create events.
     * Note: This is typically admin-only via Filament.
     */
    public function create(User $user): bool
    {
        // Event creation is handled via Filament admin
        return true;
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        // Creator or admin can update
        return true;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        // Cannot delete event with confirmed bookings
        return $event->confirmedBookings()->count() === 0;
    }

    /**
     * Determine whether the user can book the event.
     */
    public function book(User $user, Event $event): bool
    {
        // User must not be banned
        if ($user->is_banned) {
            return false;
        }

        // Event must be bookable
        return $event->isBookable();
    }

    /**
     * Determine whether the user can restore the event.
     */
    public function restore(User $user, Event $event): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the event.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $event->confirmedBookings()->count() === 0;
    }
}

