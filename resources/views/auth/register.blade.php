<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
?>

@extends('layouts.app', ['title' => '注册'])

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-black/20 backdrop-blur">
            <h1 class="text-xl font-semibold tracking-tight">注册</h1>
            <p class="mt-1 text-sm text-slate-300">创建账号后即可预约活动并在个人中心管理。</p>

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="text-sm text-slate-200">昵称</label>
                    <input name="name" type="text" autocomplete="name" required
                           value="{{ old('name') }}"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-100 placeholder:text-slate-500 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
                </div>

                <div>
                    <label class="text-sm text-slate-200">邮箱</label>
                    <input name="email" type="email" autocomplete="email" required
                           value="{{ old('email') }}"
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-100 placeholder:text-slate-500 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
                </div>

                <div>
                    <label class="text-sm text-slate-200">密码（至少 8 位）</label>
                    <input name="password" type="password" autocomplete="new-password" required
                           class="mt-2 w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-slate-100 placeholder:text-slate-500 focus:border-amber-300/40 focus:outline-none focus:ring-2 focus:ring-amber-300/20"/>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-amber-400/20 px-4 py-3 font-medium text-amber-100 ring-1 ring-amber-300/25 hover:bg-amber-400/25">
                    创建账号
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-slate-300">
                已有账号？
                <a href="{{ route('login') }}" class="text-amber-200 hover:underline">去登录</a>
            </div>
        </div>
    </div>
@endsection


