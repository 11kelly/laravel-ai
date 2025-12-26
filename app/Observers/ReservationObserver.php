<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Observers;

use App\Models\Activity;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class ReservationObserver
{
    /**
     * Handle the Reservation "updating" event.
     * Sync reserved_count when status changes via Filament admin panel.
     *
     * Note: Skip if cancelled_at is already being set (means it's from ReservationService).
     */
    public function updating(Reservation $reservation): void
    {
        // Check if status is being changed
        if (! $reservation->isDirty('status')) {
            return;
        }

        // Skip if cancelled_at is also being changed - means ReservationService already handled it
        if ($reservation->isDirty('cancelled_at')) {
            return;
        }

        $originalStatus = $reservation->getOriginal('status');
        $newStatus = $reservation->status;

        // If changing from active to cancelled (admin operation)
        if (in_array($originalStatus, ['pending', 'confirmed']) && $newStatus === 'cancelled') {
            // Safe decrement - only if reserved_count > 0
            Activity::query()
                ->where('id', $reservation->activity_id)
                ->where('reserved_count', '>', 0)
                ->decrement('reserved_count');

            $reservation->cancelled_at = now();

            Log::channel('daily')->info('Reservation cancelled via admin', [
                'reservation_id' => $reservation->id,
                'activity_id' => $reservation->activity_id,
                'user_id' => $reservation->user_id,
            ]);
        }

        // If changing from cancelled back to active (restore)
        if ($originalStatus === 'cancelled' && in_array($newStatus, ['pending', 'confirmed'])) {
            Activity::query()
                ->where('id', $reservation->activity_id)
                ->increment('reserved_count');

            $reservation->cancelled_at = null;

            Log::channel('daily')->info('Reservation restored via admin', [
                'reservation_id' => $reservation->id,
                'activity_id' => $reservation->activity_id,
                'user_id' => $reservation->user_id,
            ]);
        }
    }
}

