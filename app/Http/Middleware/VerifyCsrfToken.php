<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        // Log CSRF token verification attempt (AFTER StartSession middleware)
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
            $logFile = storage_path('logs/csrf-debug.log');
            
            try {
                $sessionId = $request->hasSession() ? $request->session()->getId() : 'NO_SESSION';
                $sessionToken = $request->hasSession() ? $request->session()->token() : 'NO_SESSION';
            } catch (\Exception $e) {
                $sessionId = 'SESSION_ERROR: ' . $e->getMessage();
                $sessionToken = 'SESSION_ERROR';
            }
            
            $requestToken = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
            $xsrfToken = $request->header('X-XSRF-TOKEN');
            
            $logData = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => $sessionId,
                'session_token' => $sessionToken !== 'NO_SESSION' && $sessionToken !== 'SESSION_ERROR' ? substr($sessionToken, 0, 40) . '...' : $sessionToken,
                'request_token' => $requestToken ? substr($requestToken, 0, 40) . '...' : 'NULL',
                'xsrf_token' => $xsrfToken ? substr($xsrfToken, 0, 40) . '...' : 'NULL',
                'session_exists' => $request->hasSession(),
                'session_started' => $request->hasSession() ? $request->session()->isStarted() : false,
                'tokens_match' => ($sessionToken !== 'NO_SESSION' && $sessionToken !== 'SESSION_ERROR' && $requestToken) ? ($sessionToken === $requestToken) : false,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'cookies' => $request->cookies->all(),
            ];
            
            // Write directly to file FIRST (most reliable)
            @file_put_contents(
                $logFile,
                "\n" . str_repeat('-', 80) . "\n" .
                date('Y-m-d H:i:s') . " - CSRF Verification Attempt (AFTER StartSession)\n" .
                json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n",
                FILE_APPEND
            );
            
            // Also try Laravel log
            try {
                Log::info('CSRF Token Verification Attempt', $logData);
            } catch (\Exception $e) {
                @file_put_contents(
                    $logFile,
                    "Laravel Log Error: " . $e->getMessage() . "\n",
                    FILE_APPEND
                );
            }
        }

        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // Log detailed information about the CSRF token mismatch
            try {
                $sessionId = $request->hasSession() ? $request->session()->getId() : 'NO_SESSION';
                $sessionToken = $request->hasSession() ? $request->session()->token() : 'NO_SESSION';
            } catch (\Exception $sessionError) {
                $sessionId = 'SESSION_ERROR: ' . $sessionError->getMessage();
                $sessionToken = 'SESSION_ERROR';
            }
            
            $requestToken = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
            $xsrfToken = $request->header('X-XSRF-TOKEN');
            
            $logData = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => $sessionId,
                'session_token' => $sessionToken ? substr($sessionToken, 0, 20) . '...' : 'NULL',
                'request_token' => $requestToken ? substr($requestToken, 0, 20) . '...' : 'NULL',
                'xsrf_token' => $xsrfToken ? substr($xsrfToken, 0, 20) . '...' : 'NULL',
                'tokens_match' => $sessionToken === $requestToken,
                'session_exists' => $request->hasSession(),
                'session_started' => $request->hasSession() ? $request->session()->isStarted() : false,
                'session_lifetime' => config('session.lifetime'),
                'session_driver' => config('session.driver'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'cookies' => $request->cookies->all(),
                'all_input' => $request->except(['password', 'password_confirmation', '_token']),
            ];
            
            // Write directly to file FIRST (most reliable)
            $logFile = storage_path('logs/csrf-debug.log');
            @file_put_contents(
                $logFile,
                "\n" . str_repeat('*', 80) . "\n" .
                date('Y-m-d H:i:s') . " - CSRF TOKEN MISMATCH - 419 ERROR\n" .
                json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n" .
                str_repeat('*', 80) . "\n",
                FILE_APPEND
            );
            
            // Also try Laravel log
            try {
                Log::error('CSRF Token Mismatch - 419 Error', $logData);
            } catch (\Exception $logError) {
                @file_put_contents(
                    $logFile,
                    "Laravel Log Error: " . $logError->getMessage() . "\n",
                    FILE_APPEND
                );
            }

            throw $e;
        }
    }
}

