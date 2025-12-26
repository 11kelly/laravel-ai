{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? '活動預約系統' }} - eBrook</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=noto-sans-tc:400,500,600,700,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Noto Sans TC', system-ui, sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-red-50 via-orange-50 to-yellow-50">
    <!-- Decorative Elements -->
    <div class="fixed top-20 left-10 text-4xl animate-wave opacity-60 pointer-events-none hidden lg:block">🏮</div>
    <div class="fixed top-40 right-10 text-3xl animate-wave delay-300 opacity-60 pointer-events-none hidden lg:block">🏮</div>
    <div class="fixed bottom-40 left-20 text-2xl animate-float opacity-40 pointer-events-none hidden lg:block">✨</div>
    <div class="fixed bottom-60 right-20 text-2xl animate-float delay-200 opacity-40 pointer-events-none hidden lg:block">✨</div>

    <!-- Navigation -->
    <nav class="sticky top-0 z-50 backdrop-blur-xl bg-gradient-to-r from-red-600/95 via-red-500/95 to-orange-500/95 shadow-lg shadow-red-500/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center shadow-lg shadow-yellow-500/30 group-hover:shadow-yellow-500/50 group-hover:scale-110 transition-all duration-300">
                            <span class="text-xl">🎉</span>
                        </div>
                        <span class="text-xl font-bold text-white drop-shadow-lg">
                            活動預約
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:items-center sm:gap-1">
                    <a href="{{ route('home') }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 {{ request()->routeIs('home') ? 'bg-white/20 text-white shadow-inner' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                        🏠 首頁
                    </a>
                    <a href="{{ route('activities.index') }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 {{ request()->routeIs('activities.*') ? 'bg-white/20 text-white shadow-inner' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                        📅 全部活動
                    </a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('my.reservations') }}" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 {{ request()->routeIs('my.*') ? 'bg-white/20 text-white shadow-inner' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                            🎫 我的預約
                        </a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-full hover:bg-white/10 transition-all duration-300">
                                <div class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center text-red-800 text-sm font-bold shadow-md">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-white hidden sm:block">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-2xl border border-red-100 py-2 z-50 overflow-hidden">
                                <a href="{{ route('my.profile') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 transition-colors">
                                    <span>👤</span> 個人資料
                                </a>
                                <a href="{{ route('my.reservations') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 transition-colors">
                                    <span>🎫</span> 我的預約
                                </a>
                                <hr class="my-2 border-red-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <span>👋</span> 登出
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white transition-colors">
                            登入
                        </a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 bg-gradient-to-r from-yellow-400 to-amber-500 text-red-800 text-sm font-bold rounded-full shadow-lg shadow-yellow-500/30 hover:shadow-yellow-500/50 hover:scale-105 transition-all duration-300">
                            ✨ 立即註冊
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 animate-slide-up">
            <div class="bg-gradient-to-r from-emerald-50 to-green-50 border-2 border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-lg" role="alert">
                <span class="text-2xl">🎊</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 animate-slide-up">
            <div class="bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-200 text-red-800 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-lg" role="alert">
                <span class="text-2xl">😅</span>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="mt-20 bg-gradient-to-r from-red-600 via-red-500 to-orange-500 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-full flex items-center justify-center shadow-lg">
                        <span class="text-xl">🎉</span>
                    </div>
                    <div>
                        <span class="font-bold text-lg">活動預約系統</span>
                        <p class="text-white/70 text-sm">讓每一次活動都精彩難忘</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-3xl">
                    <span class="animate-wave">🏮</span>
                    <span class="animate-wave delay-100">🧧</span>
                    <span class="animate-wave delay-200">🎊</span>
                </div>
                <p class="text-sm text-white/70">
                    © {{ date('Y') }} eBrook Group. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</body>
</html>
