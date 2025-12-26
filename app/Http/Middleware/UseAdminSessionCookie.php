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

class UseAdminSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $isAdminPath = str_starts_with($path, 'admin');
        $isLivewirePath = str_starts_with($path, 'livewire/');

        $referer = (string) $request->headers->get('referer');
        $refererPath = (string) (parse_url($referer, PHP_URL_PATH) ?: '');
        $isAdminReferer = str_starts_with($refererPath, '/admin');

        // Only switch the session cookie for:
        // - Filament admin panel requests (/admin/*)
        // - Livewire endpoints when triggered from the admin panel (referer: /admin/*)
        if ($isAdminPath || ($isLivewirePath && $isAdminReferer)) {
            $cookie = (string) config('session.cookie', 'laravel-session');
            $adminCookie = $cookie . '_admin';

            config([
                'session.cookie' => $adminCookie,
            ]);
        }

        return $next($request);
    }
}


