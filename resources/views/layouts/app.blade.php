<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
<div class="absolute inset-0 opacity-35 pointer-events-none"
     style="background: radial-gradient(700px circle at 20% 20%, rgba(59,130,246,0.22), transparent 55%), radial-gradient(800px circle at 80% 30%, rgba(251,191,36,0.20), transparent 55%), radial-gradient(700px circle at 50% 90%, rgba(236,72,153,0.16), transparent 55%);"></div>

<div class="relative">
    <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/60 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4 py-3">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('activities.index') }}"
                   class="flex items-center gap-2 font-semibold tracking-tight">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-400/20 text-amber-300 ring-1 ring-amber-300/30">
                        A
                    </span>
                    <span>{{ config('app.name', 'Activities') }}</span>
                </a>

                <nav class="flex items-center gap-2 text-sm">
                    <a href="{{ route('activities.index') }}"
                       class="rounded-lg px-3 py-2 text-slate-200 hover:bg-white/5">活动</a>

                    @auth
                        <a href="{{ route('me.bookings.index') }}"
                           class="rounded-lg px-3 py-2 text-slate-200 hover:bg-white/5">我的预约</a>
                        <a href="{{ route('me.profile.edit') }}"
                           class="rounded-lg px-3 py-2 text-slate-200 hover:bg-white/5">个人资料</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg px-3 py-2 text-slate-200 hover:bg-white/5">
                                退出
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-lg px-3 py-2 text-slate-200 hover:bg-white/5">登录</a>
                        <a href="{{ route('register') }}"
                           class="rounded-lg bg-amber-400/15 px-3 py-2 text-amber-200 ring-1 ring-amber-300/25 hover:bg-amber-400/20">
                            注册
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        <div class="space-y-3">
            @if (session('success'))
                <div data-flash data-timeout="3500"
                     class="transition-all rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div data-flash data-timeout="4500"
                     class="transition-all rounded-xl border border-rose-400/20 bg-rose-400/10 px-4 py-3 text-rose-100">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-rose-400/20 bg-rose-400/10 px-4 py-3 text-rose-100">
                    <div class="font-medium">请检查以下问题：</div>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="mt-6">
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-white/10 py-8 text-center text-xs text-slate-400">
        <div class="mx-auto max-w-6xl px-4">
            <span>© {{ date('Y') }} {{ config('app.name') }}</span>
        </div>
    </footer>
</div>
</body>
</html>


