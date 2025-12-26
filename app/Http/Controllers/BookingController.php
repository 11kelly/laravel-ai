<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
        // Middleware is applied in routes/web.php
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $booking = $this->bookingService->createBooking(
                (int) $validated['event_id'],
                (int) Auth::id(),
                $validated['notes'] ?? null
            );

            return redirect()
                ->route('profile.bookings.index')
                ->with('success', '预约成功！');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancel(int $id): RedirectResponse
    {
        try {
            $this->bookingService->cancelBooking($id, (int) Auth::id());

            return redirect()
                ->route('profile.bookings.index')
                ->with('success', '预约已取消');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
