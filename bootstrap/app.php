<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Custom CSRF middleware is automatically used if it exists at
        // app/Http/Middleware/VerifyCsrfToken.php
        // No additional configuration needed
        
        // Remove LogCsrfDebug from prepend - it runs before StartSession
        // The VerifyCsrfToken middleware already has logging built-in
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 419 CSRF token expired errors
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Log exception details
            $sessionId = $request->hasSession() ? $request->session()->getId() : 'NO_SESSION';
            $sessionToken = $request->hasSession() ? $request->session()->token() : 'NO_SESSION';
            
            Log::error('TokenMismatchException Caught in Exception Handler', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => $sessionId,
                'session_token' => $sessionToken ? substr($sessionToken, 0, 20) . '...' : 'NULL',
                'session_exists' => $request->hasSession(),
                'session_started' => $request->hasSession() ? $request->session()->isStarted() : false,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'exception_message' => $e->getMessage(),
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '页面已过期，请刷新页面后重试。',
                ], 419);
            }
            
            if ($request->is('register') || $request->is('login')) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'csrf' => '页面已过期，请刷新页面后重试。如果问题持续，请检查浏览器是否允许 Cookie。',
                    ]);
            }
        });
    })->create();
