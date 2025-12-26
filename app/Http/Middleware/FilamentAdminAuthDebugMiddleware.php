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

final class FilamentAdminAuthDebugMiddleware
{
    private const LOG_PATH = '/Users/lilacliu/web/laravel-ai/.cursor/debug.log';
    private const SESSION_ID_SAMPLE_LEN = 8;

    /**
     * Debug hypotheses:
     * A: APP_KEY missing/invalid => session/cookies can't be decrypted => CSRF 419
     * B: host/port mismatch => cookies not sent
     * C: session not started/persisted (db session issues) => new session each request
     * D: admin session cookie name differs => mismatch with XSRF expectations
     */
    public function handle(Request $request, Closure $next): Response
    {
        // #region agent log
        $this->log([
            'hypothesisId' => 'A|B|C|D',
            'location' => 'app/Http/Middleware/FilamentAdminAuthDebugMiddleware.php:35',
            'message' => 'admin request (pre-next)',
            'data' => $this->snapshot($request),
        ]);
        // #endregion

        $response = $next($request);

        // #region agent log
        $this->log([
            'hypothesisId' => 'A|B|C|D',
            'location' => 'app/Http/Middleware/FilamentAdminAuthDebugMiddleware.php:46',
            'message' => 'admin request (post-next)',
            'data' => [
                'status' => $response->getStatusCode(),
                'setCookieCount' => method_exists($response, 'headers') ? count($response->headers->getCookies()) : null,
            ],
        ]);
        // #endregion

        return $response;
    }

    private function snapshot(Request $request): array
    {
        $cookieNames = array_keys($request->cookies->all());
        sort($cookieNames);

        $headerNames = array_keys($request->headers->all());
        sort($headerNames);

        $sessionCookieName = (string) config('session.cookie');

        $sessionId = null;
        $sessionIdLen = null;
        $sessionIdSample = null;
        $sessionStarted = null;

        try {
            $session = $request->session();
            $sessionStarted = $session->isStarted();
            $sessionId = $session->getId();
            $sessionIdLen = is_string($sessionId) ? strlen($sessionId) : null;
            $sessionIdSample = is_string($sessionId) ? substr($sessionId, 0, self::SESSION_ID_SAMPLE_LEN) : null;
        } catch (\Throwable) {
            // session may not be initialized yet; that's exactly what we're observing.
        }

        $appKey = (string) config('app.key');

        return [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'routeName' => optional($request->route())->getName(),
            'fullUrl' => $request->fullUrl(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'port' => $request->getPort(),
            'baseUrl' => $request->getBaseUrl(),
            'refererHost' => parse_url((string) $request->headers->get('referer'), PHP_URL_HOST),
            'refererPort' => parse_url((string) $request->headers->get('referer'), PHP_URL_PORT),
            'originHost' => parse_url((string) $request->headers->get('origin'), PHP_URL_HOST),
            'originPort' => parse_url((string) $request->headers->get('origin'), PHP_URL_PORT),
            'config' => [
                'appEnv' => (string) config('app.env'),
                'appUrl' => (string) config('app.url'),
                'appKeyPresent' => $appKey !== '',
                'appKeyLooksBase64' => str_starts_with($appKey, 'base64:'),
                'sessionDriver' => (string) config('session.driver'),
                'sessionCookie' => $sessionCookieName,
                'sessionDomain' => config('session.domain'),
                'sessionSecure' => config('session.secure'),
                'sessionSameSite' => (string) config('session.same_site'),
            ],
            'request' => [
                'cookieNames' => $cookieNames,
                'headerNames' => array_slice($headerNames, 0, 40),
                'hasXsrfTokenCookie' => $request->cookies->has('XSRF-TOKEN'),
                'hasSessionCookie' => $sessionCookieName !== '' ? $request->cookies->has($sessionCookieName) : null,
                'hasXCsrfTokenHeader' => $request->headers->has('x-csrf-token'),
                'hasXXsrfTokenHeader' => $request->headers->has('x-xsrf-token'),
            ],
            'session' => [
                'started' => $sessionStarted,
                'idLen' => $sessionIdLen,
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
            // If logging fails, do not impact the request.
        }
    }
}


