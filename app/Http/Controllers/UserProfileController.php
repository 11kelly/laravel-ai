<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(Request $request): View|JsonResponse
    {
        $user = Auth::user();

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_path' => $user->avatar_path,
                'created_at' => $user->created_at->toIso8601String(),
            ]);
        }

        return view('user.profile.show', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:50'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]);
        }

        return redirect()->route('user.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}

