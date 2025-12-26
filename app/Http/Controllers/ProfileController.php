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

class ProfileController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {
        // Middleware is applied in routes/web.php
    }

    /**
     * Display user bookings
     */
    public function bookings(Request $request): View
    {
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 15);

        $bookings = $this->bookingService->getUserBookings(
            (int) Auth::id(),
            $status,
            $perPage
        );

        return view('profile.bookings', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Show the profile edit form
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user profile
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|string|min:2|max:50',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
        ]);

        try {
            $user->update($validated);

            return redirect()
                ->route('profile.edit')
                ->with('success', '资料更新成功');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => '更新失败，请重试']);
        }
    }
}
