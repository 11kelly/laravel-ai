<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::query()
            ->where('user_id', '=', Auth::id())
            ->with(['activity'])
            ->orderByDesc('booked_at')
            ->paginate(20);

        return view('me.bookings.index', [
            'bookings' => $bookings,
        ]);
    }
}


