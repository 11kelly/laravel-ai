<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LivewireAuthDebugMiddleware
{
    private const LOG_PATH = '/Users/lilacliu/web/laravel-ai/.cursor/debug.log';
    private const SESSION_ID_SAMPLE_LEN = 8;

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $isLivewire = str_starts_with($path, 'livewire/');

        if ($isLivewire) {
            // #region agent log
            $this->log([
                'hypothesisId' => 'B|C|D',
                'location' => 'app/Http/Middleware/LivewireAuthDebugMiddleware.php:27',
                'message' => 'livewire request (pre-next)',
                'data' => $this->snapshot($request),
            ]);
            // #endregion
        }

        $response = $next($request);

        if ($isLivewire) {
            // #region agent log
            $this->log([
                'hypothesisId' => 'B|C|D',
                'location' => 'app/Http/Middleware/LivewireAuthDebugMiddleware.php:43',
                'message' => 'livewire request (post-next)',
                'data' => [
                    'path' => $path,
                    'routeName' => optional($request->route())->getName(),
                    'status' => $response->getStatusCode(),
                ],
            ]);
            // #endregion
        }

        return $response;
    }

    private function snapshot(Request $request): array
    {
        $cookieNames = array_keys($request->cookies->all());
        sort($cookieNames);

        $headerNames = array_keys($request->headers->all());
        sort($headerNames);

        $sessionCookieName = (string) config('session.cookie');
        $defaultSessionCookieName = 'laravel-session';

        $sessionIdSample = null;
        $sessionStarted = null;

        try {
            $session = $request->session();
            $sessionStarted = $session->isStarted();
            $sessionId = $session->getId();
            $sessionIdSample = is_string($sessionId) ? substr($sessionId, 0, self::SESSION_ID_SAMPLE_LEN) : null;
        } catch (\Throwable) {
            // ignore
        }

        $referer = (string) $request->headers->get('referer');
        $refererHost = parse_url($referer, PHP_URL_HOST);
        $refererPort = parse_url($referer, PHP_URL_PORT);
        $refererPath = parse_url($referer, PHP_URL_PATH);

        $origin = (string) $request->headers->get('origin');
        $originHost = parse_url($origin, PHP_URL_HOST);
        $originPort = parse_url($origin, PHP_URL_PORT);

        $hasCsrfInputToken = false;
        try {
            $hasCsrfInputToken = $request->has('_token') && is_string($request->input('_token')) && $request->input('_token') !== '';
        } catch (\Throwable) {
            // ignore
        }

        return [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'routeName' => optional($request->route())->getName(),
            'fullUrl' => $request->fullUrl(),
            'host' => $request->getHost(),
            'port' => $request->getPort(),
            'refererHost' => $refererHost,
            'refererPort' => $refererPort,
            'refererPath' => $refererPath,
            'originHost' => $originHost,
            'originPort' => $originPort,
            'config' => [
                'sessionCookie' => $sessionCookieName,
                'sessionDriver' => (string) config('session.driver'),
                'appUrl' => (string) config('app.url'),
            ],
            'request' => [
                'cookieNames' => $cookieNames,
                'headerNames' => array_slice($headerNames, 0, 40),
                'hasCookieHeader' => $request->headers->has('cookie'),
                'cookieHeaderLen' => $request->headers->has('cookie') ? strlen((string) $request->headers->get('cookie')) : null,
                'hasDefaultSessionCookie' => $request->cookies->has($defaultSessionCookieName),
                'hasConfiguredSessionCookie' => $sessionCookieName !== '' ? $request->cookies->has($sessionCookieName) : null,
                'hasAdminSessionCookie' => $request->cookies->has($defaultSessionCookieName . '_admin'),
                'hasXsrfTokenCookie' => $request->cookies->has('XSRF-TOKEN'),
                'hasXXsrfTokenHeader' => $request->headers->has('x-xsrf-token'),
                'hasXCsrfTokenHeader' => $request->headers->has('x-csrf-token'),
                'hasCsrfInputToken' => $hasCsrfInputToken,
                'hasXLivewireHeader' => $request->headers->has('x-livewire'),
                'secFetchSite' => $request->headers->get('sec-fetch-site'),
                'secFetchMode' => $request->headers->get('sec-fetch-mode'),
                'contentType' => $request->headers->get('content-type'),
            ],
            'session' => [
                'started' => $sessionStarted,
                'idSample' => $sessionIdSample,
            ],
        ];
    }

    private function log(array $payload): void
    {
        $entry = [
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'timestamp' => (int) (microtime(true) * 1000),
            'location' => (string) ($payload['location'] ?? 'unknown'),
            'message' => (string) ($payload['message'] ?? ''),
            'hypothesisId' => (string) ($payload['hypothesisId'] ?? ''),
            'data' => $payload['data'] ?? new \stdClass(),
        ];

        try {
            file_put_contents(self::LOG_PATH, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
        } catch (\Throwable) {
            // ignore
        }
    }
}


