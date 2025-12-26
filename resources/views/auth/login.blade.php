<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
?>

@extends('layouts.app', ['title' => '登录'])

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-black/20 backdrop-blur">
            <h1 class="text-xl font-semibold tracking-tight">登录</h1>
            <p class="mt-1 text-sm text-slate-300">欢迎回来，登录后即可预约与管理活动。</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="text-sm text-slate-200">邮箱</label>
                    <input name="email" type="email" autocomplete="email" required
                           value="{{ old('email') }}"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-100 placeholder:text-slate-500 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
                </div>

                <div>
                    <label class="text-sm text-slate-200">密码</label>
                    <input name="password" type="password" autocomplete="current-password" required
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-100 placeholder:text-slate-500 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-amber-400/20 px-4 py-3 font-medium text-amber-100 ring-1 ring-amber-300/25 hover:bg-amber-400/25">
                    登录
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-slate-300">
                还没有账号？
                <a href="{{ route('register') }}" class="text-amber-200 hover:underline">去注册</a>
            </div>
        </div>
    </div>
@endsection


