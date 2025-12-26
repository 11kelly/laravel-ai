<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingCancellationService
{
    /**
     * Cancel a booking
     *
     * @throws \Exception
     */
    public function cancelBooking(Booking $booking, User $actor, ?string $reason = null): Booking
    {
        $is_admin = $actor->isAdmin();
        
        if (!$is_admin && $booking->user_id !== $actor->id) {
            throw new \Exception('You do not have permission to cancel this booking.');
        }

        if (!$booking->canBeCancelled()) {
            if ($booking->status === BookingStatus::CANCELLED) {
                throw new \Exception('This booking has already been cancelled.');
            }

            if ($booking->event->start_time->isPast()) {
                throw new \Exception('Cannot cancel booking for an event that has already started.');
            }

            throw new \Exception('This booking cannot be cancelled.');
        }

        return DB::transaction(function () use ($booking, $actor, $is_admin, $reason) {
            $event = $booking->event()->lockForUpdate()->first();

            $booking->update([
                'status' => BookingStatus::CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $is_admin ? 'admin' : 'user',
                'cancellation_reason' => $reason,
            ]);

            // 防护性检查：避免 UNSIGNED 下溢
            if ($event->booked_count >= $booking->participants_count) {
            $event->decrement('booked_count', $booking->participants_count);
            } else {
                // 数据不一致警告
                Log::warning('Booking count inconsistency detected', [
                    'event_id' => $event->id,
                    'current_booked_count' => $event->booked_count,
                    'trying_to_refund' => $booking->participants_count,
                    'booking_id' => $booking->id,
                ]);
                
                // 直接设置为 0
                $event->update(['booked_count' => 0]);
            }

            Log::info('Booking cancelled', [
                'booking_id' => $booking->id,
                'event_id' => $event->id,
                'user_id' => $booking->user_id,
                'cancelled_by_user_id' => $actor->id,
                'is_admin' => $is_admin,
                'reason' => $reason,
                'refunded_slots' => $booking->participants_count,
            ]);

            return $booking->load('event', 'user');
        });
    }
}

