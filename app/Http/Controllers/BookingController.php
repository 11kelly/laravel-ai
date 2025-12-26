<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
        //
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->bookingService->createBooking(
                Auth::user(),
                $event,
                $request->input('notes')
            );

            return redirect()
                ->route('events.show', $event)
                ->with('success', '预约成功！');
        } catch (BookingException $e) {
            return redirect()
                ->route('events.show', $event)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ensure the booking belongs to the authenticated user
        if ($booking->user_id !== $user->id) {
            abort(403, '无权访问此预约');
        }

        try {
            $this->bookingService->cancelBooking($booking);

            return redirect()
                ->route('profile.bookings')
                ->with('success', '预约已取消');
        } catch (BookingException $e) {
            return redirect()
                ->route('profile.bookings')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Handle DELETE request for cancel booking.
     */
    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        return $this->cancel($request, $booking);
    }
}

