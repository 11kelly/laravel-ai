{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<x-layouts.app>
    <x-slot:title>登入</x-slot:title>

    <div class="min-h-[80vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <!-- Decorative Elements -->
        <div class="fixed top-32 left-20 text-4xl animate-wave opacity-40 pointer-events-none hidden lg:block">🏮</div>
        <div class="fixed bottom-32 right-20 text-4xl animate-wave delay-200 opacity-40 pointer-events-none hidden lg:block">🏮</div>

        <div class="w-full max-w-md animate-bounce-in">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-red-500 to-orange-500 rounded-2xl shadow-2xl shadow-red-500/40 mb-6">
                    <span class="text-4xl">🔐</span>
                </div>
                <h2 class="text-3xl font-black text-gray-800 mb-2">歡迎回來</h2>
                <p class="text-gray-500">登入您的帳戶，開始預約精彩活動</p>
                <div class="flex justify-center gap-2 mt-4 text-2xl">
                    <span class="animate-wave">🏮</span>
                    <span class="animate-wave delay-100">🧧</span>
                    <span class="animate-wave delay-200">🏮</span>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl border-2 border-red-100 shadow-2xl shadow-red-500/10 p-8">
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                            📧 Email
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors @error('email') border-red-500 bg-red-50 @enderror" placeholder="請輸入您的 Email">
                        @error('email')
                            <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                            🔑 密碼
                        </label>
                        <input type="password" name="password" id="password" required class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors @error('password') border-red-500 bg-red-50 @enderror" placeholder="請輸入密碼">
                        @error('password')
                            <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="remember" class="w-5 h-5 rounded border-2 border-red-300 text-red-600 focus:ring-red-500 cursor-pointer">
                            <span class="text-sm text-gray-600 group-hover:text-red-600 transition-colors">記住我</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-red-500 via-red-600 to-orange-500 text-white font-bold text-lg rounded-xl shadow-xl shadow-red-500/30 hover:shadow-red-500/50 hover:scale-[1.02] transition-all relative overflow-hidden group">
                        <span class="relative z-10 flex items-center justify-center gap-2">
                            <span class="group-hover:animate-bounce">🎯</span> 登入
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t-2 border-dashed border-red-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-4 bg-white text-sm text-gray-500">還沒有帳號？</span>
                    </div>
                </div>

                <!-- Register Link -->
                <a href="{{ route('register') }}" class="w-full flex items-center justify-center gap-2 py-4 border-2 border-red-200 text-red-600 font-bold rounded-xl hover:bg-red-50 hover:border-red-300 transition-all">
                    <span>✨</span> 立即註冊
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
