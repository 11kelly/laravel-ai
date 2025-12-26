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

    <title>@yield('title', '活动预约平台') - {{ config('app.name', 'EventHub') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fdf4ff',
                            100: '#fae8ff',
                            200: '#f5d0fe',
                            300: '#f0abfc',
                            400: '#e879f9',
                            500: '#d946ef',
                            600: '#c026d3',
                            700: '#a21caf',
                            800: '#86198f',
                            900: '#701a75',
                            950: '#4a044e',
                        },
                        accent: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                    },
                },
            },
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }

        .gradient-mesh {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f97316 100%);
        }

        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #d946ef 50%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #d946ef 0%, #f97316 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 40px rgba(217, 70, 239, 0.4);
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900 min-h-screen flex flex-col">
    {{-- Navigation --}}
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200/50" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gradient">EventHub</span>
                </a>

                {{-- Desktop Navigation --}}
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-slate-600 hover:text-primary-600 font-medium transition-colors {{ request()->routeIs('home') ? 'text-primary-600' : '' }}">
                        首页
                    </a>
                    <a href="{{ route('activities.index') }}" class="text-slate-600 hover:text-primary-600 font-medium transition-colors {{ request()->routeIs('activities.*') ? 'text-primary-600' : '' }}">
                        活动
                    </a>
                </div>

                {{-- Auth Buttons --}}
                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <a href="{{ route('user.dashboard') }}" class="text-slate-600 hover:text-primary-600 font-medium transition-colors flex items-center space-x-2">
                            <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-primary-100">
                            <span>{{ auth()->user()->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-slate-500 hover:text-slate-700 text-sm">
                                登出
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-primary-600 font-medium transition-colors">
                            登录
                        </a>
                        <a href="{{ route('register') }}" class="btn-gradient text-white px-5 py-2.5 rounded-xl font-semibold text-sm">
                            免费注册
                        </a>
                    @endauth
                </div>

                {{-- Mobile Menu Button --}}
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t border-slate-200 bg-white">
            <div class="px-4 py-4 space-y-3">
                <a href="{{ route('home') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100 font-medium">首页</a>
                <a href="{{ route('activities.index') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100 font-medium">活动</a>
                @auth
                    <a href="{{ route('user.dashboard') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100 font-medium">个人中心</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 rounded-lg hover:bg-slate-100 text-slate-500">登出</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-4 py-2 rounded-lg hover:bg-slate-100 font-medium">登录</a>
                    <a href="{{ route('register') }}" class="block px-4 py-2 rounded-lg bg-gradient-to-r from-primary-500 to-accent-500 text-white text-center font-semibold">免费注册</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-20 right-4 z-50 bg-green-500 text-white px-6 py-4 rounded-xl shadow-lg flex items-center space-x-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="ml-2 hover:opacity-75">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed top-20 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-xl shadow-lg flex items-center space-x-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="ml-2 hover:opacity-75">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- Main Content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-300 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">EventHub</span>
                    </div>
                    <p class="text-slate-400 max-w-md">
                        发现精彩活动，开启非凡体验。EventHub 为您提供最便捷的活动预约服务。
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">快速链接</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="hover:text-primary-400 transition-colors">首页</a></li>
                        <li><a href="{{ route('activities.index') }}" class="hover:text-primary-400 transition-colors">活动列表</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">联系我们</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li>contact@eventhub.com</li>
                        <li>+886 2 1234 5678</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 mt-8 pt-8 text-center text-slate-500 text-sm">
                <p>© {{ date('Y') }} EventHub. Developed by eBrook Group.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

