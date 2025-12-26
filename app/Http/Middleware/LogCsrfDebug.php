<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogCsrfDebug
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Log ALL POST requests to register route BEFORE CSRF verification
        if ($request->isMethod('POST') && ($request->is('register') || $request->is('*register*'))) {
            $logFile = storage_path('logs/csrf-debug.log');
            
            try {
                $sessionId = $request->hasSession() ? $request->session()->getId() : 'NO_SESSION';
                $sessionToken = $request->hasSession() ? $request->session()->token() : 'NO_SESSION';
            } catch (\Exception $e) {
                $sessionId = 'SESSION_ERROR: ' . $e->getMessage();
                $sessionToken = 'SESSION_ERROR';
            }
            
            $requestToken = $request->input('_token') ?: 'NO_TOKEN_INPUT';
            $csrfHeader = $request->header('X-CSRF-TOKEN') ?: 'NO_CSRF_HEADER';
            $xsrfHeader = $request->header('X-XSRF-TOKEN') ?: 'NO_XSRF_HEADER';
            
            $logData = [
                'timestamp' => now()->toDateTimeString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'has_session' => $request->hasSession(),
                'session_id' => $sessionId,
                'session_token' => $sessionToken ? substr($sessionToken, 0, 30) . '...' : 'NULL',
                'request_token' => $requestToken !== 'NO_TOKEN_INPUT' ? substr($requestToken, 0, 30) . '...' : 'NO_TOKEN',
                'csrf_header' => $csrfHeader !== 'NO_CSRF_HEADER' ? substr($csrfHeader, 0, 30) . '...' : 'NO_HEADER',
                'xsrf_header' => $xsrfHeader !== 'NO_XSRF_HEADER' ? substr($xsrfHeader, 0, 30) . '...' : 'NO_HEADER',
                'tokens_match' => ($sessionToken && $requestToken !== 'NO_TOKEN_INPUT') ? ($sessionToken === $requestToken) : false,
                'cookies' => $request->cookies->all(),
                'all_headers' => $request->headers->all(),
            ];
            
            // Write directly to file FIRST (most reliable)
            $logContent = "\n" . str_repeat('=', 80) . "\n" .
                date('Y-m-d H:i:s') . " - Register POST Request (BEFORE CSRF)\n" .
                json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            
            @file_put_contents($logFile, $logContent, FILE_APPEND);
            
            // Also try Laravel log
            try {
                Log::info('Register POST Request Debug (Before CSRF)', $logData);
            } catch (\Exception $e) {
                @file_put_contents(
                    $logFile,
                    "Laravel Log Error: " . $e->getMessage() . "\n",
                    FILE_APPEND
                );
            }
        }
        
        return $next($request);
    }
}

