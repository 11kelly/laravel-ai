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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    public function dashboard(): View
    {
        $user = auth()->user();

        $upcomingBookings = $this->bookingService->getUserBookings($user, 'upcoming', 5);
        $totalBookings = $user->bookings()->count();
        $confirmedBookings = $user->bookings()->confirmed()->count();
        $cancelledBookings = $user->bookings()->cancelled()->count();

        return view('user.dashboard', [
            'user' => $user,
            'upcomingBookings' => $upcomingBookings,
            'totalBookings' => $totalBookings,
            'confirmedBookings' => $confirmedBookings,
            'cancelledBookings' => $cancelledBookings,
        ]);
    }

    public function bookings(Request $request): View
    {
        $user = auth()->user();
        $status = $request->get('status', 'all');

        $bookings = $this->bookingService->getUserBookings($user, $status, 10);

        return view('user.bookings', [
            'bookings' => $bookings,
            'currentStatus' => $status,
        ]);
    }

    public function profile(): View
    {
        return view('user.profile', [
            'user' => auth()->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', '资料更新成功');
    }

    public function password(): View
    {
        return view('user.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', '密码修改成功');
    }
}

