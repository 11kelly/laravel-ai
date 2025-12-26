<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'participants' => 'nullable|integer|min:1|max:10',
            'remarks' => 'nullable|string|max:500',
        ]);

        $activity = Activity::findOrFail($validated['activity_id']);
        $participants = (int) ($validated['participants'] ?? 1);
        $remarks = $validated['remarks'] ?? null;

        $result = $this->bookingService->createBooking(
            auth()->user(),
            $activity,
            $participants,
            $remarks
        );

        if ($result['success']) {
            return redirect()
                ->route('user.bookings')
                ->with('success', '预约成功！预约编号：' . $result['booking']->booking_code);
        }

        return back()->with('error', $result['message']);
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $reason = $request->input('cancellation_reason');
        $result = $this->bookingService->cancelBooking($booking, $reason);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}

