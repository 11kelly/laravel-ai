<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 确保前端路由只允许普通用户访问
 * 
 * 如果是 admin 用户误入前端路由，将其重定向到后台
 */
final class EnsureFrontendUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 如果用户已认证且是 admin，重定向到后台
        if ($request->user() && $request->user()->isAdmin()) {
            return redirect('/admin');
        }

        return $next($request);
    }
}

