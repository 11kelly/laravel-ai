<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

final class LocaleController extends Controller
{
    /**
     * Switch application locale.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $supported_locales = ['en', 'zh_CN'];
        
        if (!in_array($locale, $supported_locales, true)) {
            $locale = config('app.locale', 'en');
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        // 返回到上一页或首页
        return redirect()->back();
    }
}

