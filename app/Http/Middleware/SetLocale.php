<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 支持的语言列表
        $supported_locales = ['en', 'zh_CN'];
        
        // 优先从请求参数获取语言
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if (in_array($locale, $supported_locales, true)) {
                App::setLocale($locale);
                Session::put('locale', $locale);
            }
        }
        // 其次从 Session 获取
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
            if (in_array($locale, $supported_locales, true)) {
                App::setLocale($locale);
            }
        }
        // 最后使用配置的默认语言
        else {
            App::setLocale(config('app.locale', 'en'));
        }

        return $next($request);
    }
}

