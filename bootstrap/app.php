<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use App\Http\Middleware\LivewireAuthDebugMiddleware;
use App\Http\Middleware\UseAdminSessionCookie;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands()
    ->withMiddleware(function (Middleware $middleware): void {
        // Ensure we capture runtime evidence for Livewire auth/CSRF issues.
        $middleware->append(LivewireAuthDebugMiddleware::class);
        // Make sure admin-panel initiated Livewire requests use the same session cookie
        // as the Filament admin panel, so CSRF/session stays consistent.
        $middleware->prependToGroup('web', UseAdminSessionCookie::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (TokenMismatchException $e): void {
            // #region agent log
            $logPath = '/Users/lilacliu/web/laravel-ai/.cursor/debug.log';
            $request = request();

            $entry = [
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'A|B|C|D',
                'timestamp' => (int) (microtime(true) * 1000),
                'location' => 'bootstrap/app.php:reportable(TokenMismatchException)',
                'message' => 'TokenMismatchException reported (likely 419)',
                'data' => [
                    'path' => method_exists($request, 'path') ? $request->path() : null,
                    'method' => method_exists($request, 'getMethod') ? $request->getMethod() : null,
                    'host' => method_exists($request, 'getHost') ? $request->getHost() : null,
                    'port' => method_exists($request, 'getPort') ? $request->getPort() : null,
                    'fullUrl' => method_exists($request, 'fullUrl') ? $request->fullUrl() : null,
                    'routeName' => optional($request->route())->getName(),
                    'config' => [
                        'appEnv' => (string) config('app.env'),
                        'appUrl' => (string) config('app.url'),
                        'appKeyPresent' => ((string) config('app.key')) !== '',
                        'sessionDriver' => (string) config('session.driver'),
                        'sessionCookie' => (string) config('session.cookie'),
                    ],
                    'request' => [
                        'cookieNames' => method_exists($request, 'cookies') ? array_keys($request->cookies->all()) : [],
                        'hasXCsrfTokenHeader' => method_exists($request, 'headers') ? $request->headers->has('x-csrf-token') : null,
                        'hasXXsrfTokenHeader' => method_exists($request, 'headers') ? $request->headers->has('x-xsrf-token') : null,
                        'hasXsrfTokenCookie' => method_exists($request, 'cookies') ? $request->cookies->has('XSRF-TOKEN') : null,
                    ],
                ],
            ];

            try {
                file_put_contents($logPath, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
            } catch (\Throwable) {
                // ignore
            }
            // #endregion
        });
    })->create();
