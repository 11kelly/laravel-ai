<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => '登录信息不匹配。',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // 仅登出前台 Guard
        Auth::guard('web')->logout();

        // 重新生成 Token 防止 CSRF 固定攻击，但不销毁 Session 数据
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
