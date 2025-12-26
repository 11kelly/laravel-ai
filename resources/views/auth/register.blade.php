{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<x-layouts.app>
    <x-slot:title>註冊</x-slot:title>

    <div class="min-h-[80vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <!-- Decorative Elements -->
        <div class="fixed top-32 left-20 text-4xl animate-wave opacity-40 pointer-events-none hidden lg:block">🏮</div>
        <div class="fixed bottom-32 right-20 text-4xl animate-wave delay-200 opacity-40 pointer-events-none hidden lg:block">🏮</div>

        <div class="w-full max-w-md animate-bounce-in">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-2xl shadow-2xl shadow-amber-500/40 mb-6">
                    <span class="text-4xl">🎉</span>
                </div>
                <h2 class="text-3xl font-black text-gray-800 mb-2">加入我們</h2>
                <p class="text-gray-500">建立帳戶，開啟您的精彩活動之旅</p>
                <div class="flex justify-center gap-2 mt-4 text-2xl">
                    <span class="animate-wave">🎊</span>
                    <span class="animate-wave delay-100">✨</span>
                    <span class="animate-wave delay-200">🎊</span>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl border-2 border-red-100 shadow-2xl shadow-red-500/10 p-8">
                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">
                            👤 姓名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors @error('name') border-red-500 bg-red-50 @enderror" placeholder="請輸入您的姓名">
                        @error('name')
                            <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                            📧 Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors @error('email') border-red-500 bg-red-50 @enderror" placeholder="請輸入您的 Email">
                        @error('email')
                            <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">
                            📱 電話
                        </label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors @error('phone') border-red-500 bg-red-50 @enderror" placeholder="例：0912345678（選填）">
                        @error('phone')
                            <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                            🔑 密碼 <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors @error('password') border-red-500 bg-red-50 @enderror" placeholder="請設定密碼（至少 8 個字元）">
                        @error('password')
                            <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                                <span>⚠️</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">
                            ✅ 確認密碼 <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors" placeholder="請再次輸入密碼">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-500 text-white font-bold text-lg rounded-xl shadow-xl shadow-amber-500/30 hover:shadow-amber-500/50 hover:scale-[1.02] transition-all relative overflow-hidden group">
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                <span class="group-hover:rotate-12 transition-transform">🎁</span> 免費註冊
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t-2 border-dashed border-red-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-4 bg-white text-sm text-gray-500">已有帳號？</span>
                    </div>
                </div>

                <!-- Login Link -->
                <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 py-4 border-2 border-red-200 text-red-600 font-bold rounded-xl hover:bg-red-50 hover:border-red-300 transition-all">
                    <span>🔐</span> 立即登入
                </a>
            </div>

            <!-- Benefits -->
            <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                <div class="p-3 bg-white/60 backdrop-blur-sm rounded-2xl border border-red-100">
                    <span class="text-2xl block mb-1">⚡</span>
                    <span class="text-xs text-gray-600 font-medium">快速預約</span>
                </div>
                <div class="p-3 bg-white/60 backdrop-blur-sm rounded-2xl border border-red-100">
                    <span class="text-2xl block mb-1">🔔</span>
                    <span class="text-xs text-gray-600 font-medium">即時通知</span>
                </div>
                <div class="p-3 bg-white/60 backdrop-blur-sm rounded-2xl border border-red-100">
                    <span class="text-2xl block mb-1">🎫</span>
                    <span class="text-xs text-gray-600 font-medium">管理預約</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
