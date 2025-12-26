<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingCancellationService;
use App\Services\EventBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function __construct(
        private EventBookingService $bookingService,
        private BookingCancellationService $cancellationService
    ) {
        $this->middleware('auth');
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'participants_count' => 'required|integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $event = Event::findOrFail($request->event_id);
            $user = $request->user();

            // Check if user is banned
            if ($user->is_banned) {
                return redirect()->back()
                    ->with('error', 'Your account has been banned. You cannot create bookings.');
            }

            // Check recent booking attempts
            if (!$this->bookingService->checkRecentBookingAttempts($user, $event)) {
                return redirect()->back()
                    ->with('error', 'Please wait a few seconds before trying again.');
            }

            $booking = $this->bookingService->createBooking(
                $event,
                $user,
                (int) $request->participants_count,
                $request->notes
            );

            Log::info('Booking created via web', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
            ]);

            return redirect()->route('profile.index')
                ->with('success', 'Booking created successfully!');

        } catch (\Exception $e) {
            Log::warning('Booking creation failed via web', [
                'event_id' => $request->event_id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a booking.
     */
    public function destroy(string $id): RedirectResponse
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return redirect()->route('profile.index')
                ->with('error', 'Booking not found.');
        }

        $user = auth()->user();

        // Check ownership
        if ($booking->user_id !== $user->id) {
            return redirect()->route('profile.index')
                ->with('error', 'You do not have permission to cancel this booking.');
        }

        // Check if already cancelled
        if ($booking->status === 'cancelled') {
            return redirect()->route('profile.index')
                ->with('info', 'This booking has already been cancelled.');
        }

        try {
            $this->cancellationService->cancelBooking($booking, $user);

            Log::info('Booking cancelled via web', [
                'booking_id' => $booking->id,
                'user_id' => $user->id,
            ]);

            return redirect()->route('profile.index')
                ->with('success', 'Booking cancelled successfully.');

        } catch (\Exception $e) {
            Log::warning('Booking cancellation failed via web', [
                'booking_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('profile.index')
                ->with('error', $e->getMessage());
        }
    }
}

