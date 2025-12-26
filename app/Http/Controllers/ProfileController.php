<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index(): View
    {
        return view('profile.index', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->fill([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
        ]);
        $user->save();

        return redirect()
            ->route('profile.index')
            ->with('success', '资料已更新');
    }

    /**
     * Display the user's bookings.
     */
    public function bookings(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $bookings = $user
            ->bookings()
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('profile.bookings', [
            'bookings' => $bookings,
        ]);
    }
}

