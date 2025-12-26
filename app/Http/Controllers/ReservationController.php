<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\Activity\ReservationNotCancellableException;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService,
    ) {}

    /**
     * Display user's reservations.
     */
    public function index(): View
    {
        $reservations = $this->reservationService->getUserReservations(Auth::user(), 15);

        return view('my.reservations', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Reservation $reservation): RedirectResponse
    {
        // Authorization check
        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $this->reservationService->cancelReservation($reservation);

            return back()->with('success', '預約已取消');
        } catch (ReservationNotCancellableException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

