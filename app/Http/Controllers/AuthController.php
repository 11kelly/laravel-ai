<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegisterForm(Request $request): View
    {
        $sessionId = $request->hasSession() ? $request->session()->getId() : 'NO_SESSION';
        $sessionToken = $request->hasSession() ? $request->session()->token() : 'NO_SESSION';
        
        Log::info('Registration Form Accessed', [
            'url' => $request->fullUrl(),
            'session_id' => $sessionId,
            'session_token' => $sessionToken ? substr($sessionToken, 0, 20) . '...' : 'NULL',
            'session_exists' => $request->hasSession(),
            'session_started' => $request->hasSession() ? $request->session()->isStarted() : false,
            'session_lifetime' => config('session.lifetime'),
            'session_driver' => config('session.driver'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'cookies' => $request->cookies->all(),
        ]);
        
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $sessionId = $request->hasSession() ? $request->session()->getId() : 'NO_SESSION';
        $sessionToken = $request->hasSession() ? $request->session()->token() : 'NO_SESSION';
        $requestToken = $request->input('_token');
        
        Log::info('Registration Request Received', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'session_id' => $sessionId,
            'session_token' => $sessionToken ? substr($sessionToken, 0, 20) . '...' : 'NULL',
            'request_token' => $requestToken ? substr($requestToken, 0, 20) . '...' : 'NULL',
            'tokens_match' => $sessionToken === $requestToken,
            'session_exists' => $request->hasSession(),
            'session_started' => $request->hasSession() ? $request->session()->isStarted() : false,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'input_data' => $request->except(['password', 'password_confirmation', '_token']),
        ]);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', 'confirmed'],
        ], [
            'password.regex' => 'The password must contain at least one letter and one number.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        Log::info('User registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->route('user.dashboard');
    }

    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $key = 'login.'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ]);

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $credentials['remember'] ?? false)) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            Log::info('User logged in successfully', [
                'user_id' => Auth::id(),
                'email' => $credentials['email'],
                'ip' => $request->ip(),
            ]);

            return redirect()->intended(route('user.dashboard'));
        }

        RateLimiter::hit($key, 60);

        Log::warning('Login attempt failed', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('activities.index');
    }
}

