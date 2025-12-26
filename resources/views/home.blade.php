{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<x-layouts.app>
    <x-slot:title>首頁</x-slot:title>

    <!-- Hero Section -->
    <section class="relative overflow-hidden min-h-[600px] flex items-center">
        <!-- Animated Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-red-600 via-red-500 to-orange-500"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-20 left-10 w-32 h-32 bg-yellow-400 rounded-full blur-3xl animate-float"></div>
            <div class="absolute top-40 right-20 w-40 h-40 bg-orange-400 rounded-full blur-3xl animate-float delay-200"></div>
            <div class="absolute bottom-20 left-1/3 w-36 h-36 bg-red-400 rounded-full blur-3xl animate-float delay-400"></div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-10 left-[10%] text-5xl animate-wave opacity-80">🏮</div>
            <div class="absolute top-20 right-[15%] text-4xl animate-wave delay-100 opacity-80">🏮</div>
            <div class="absolute bottom-32 left-[20%] text-6xl animate-float opacity-60">🎊</div>
            <div class="absolute top-1/3 right-[10%] text-5xl animate-float delay-300 opacity-60">🎉</div>
            <div class="absolute bottom-20 right-[25%] text-4xl animate-wave delay-200 opacity-70">🧧</div>
            <div class="absolute top-1/2 left-[5%] text-3xl animate-float delay-100 opacity-50">✨</div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-medium mb-8 animate-bounce-in">
                    <span class="animate-pulse">🔥</span>
                    <span>精彩活動火熱進行中</span>
                    <span class="animate-pulse">🔥</span>
                </div>

                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white mb-6 tracking-tight animate-slide-up">
                    探索精彩活動
                    <span class="block mt-2 bg-gradient-to-r from-yellow-300 via-amber-300 to-yellow-300 bg-clip-text text-transparent drop-shadow-lg">
                        開啟美好體驗
                    </span>
                </h1>

                <p class="text-lg sm:text-xl text-white/90 max-w-2xl mx-auto mb-10 animate-slide-up delay-100" style="animation-delay: 0.1s; animation-fill-mode: forwards; opacity: 0;">
                    發現您感興趣的活動，一鍵預約，輕鬆參與。<br>
                    我們為您精選各類精彩活動，讓生活更加豐富多彩！
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up delay-200" style="animation-delay: 0.2s; animation-fill-mode: forwards; opacity: 0;">
                    <a href="{{ route('activities.index') }}" class="group inline-flex items-center justify-center gap-3 px-8 py-4 bg-white text-red-600 font-bold text-lg rounded-full shadow-2xl shadow-red-900/30 hover:shadow-red-900/50 hover:scale-105 transition-all duration-300">
                        <span class="group-hover:animate-bounce">🎯</span>
                        瀏覽全部活動
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-yellow-400 to-amber-500 text-red-800 font-bold text-lg rounded-full shadow-2xl shadow-yellow-500/30 hover:shadow-yellow-500/50 hover:scale-105 transition-all duration-300">
                            <span class="group-hover:rotate-12 transition-transform">✨</span>
                            立即註冊
                        </a>
                    @endguest
                </div>

                <!-- Stats -->
                <div class="flex flex-wrap justify-center gap-8 mt-16 animate-slide-up delay-300" style="animation-delay: 0.3s; animation-fill-mode: forwards; opacity: 0;">
                    <div class="text-center">
                        <div class="text-4xl font-black text-white">{{ $featuredActivities->count() }}+</div>
                        <div class="text-white/70 text-sm mt-1">精彩活動</div>
                    </div>
                    <div class="w-px h-12 bg-white/30"></div>
                    <div class="text-center">
                        <div class="text-4xl font-black text-white">100%</div>
                        <div class="text-white/70 text-sm mt-1">好評推薦</div>
                    </div>
                    <div class="w-px h-12 bg-white/30"></div>
                    <div class="text-center">
                        <div class="text-4xl font-black text-white">24/7</div>
                        <div class="text-white/70 text-sm mt-1">線上預約</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wave decoration -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="rgb(254 242 242)"/>
            </svg>
        </div>
    </section>

    <!-- Featured Activities -->
    <section class="py-20 sm:py-28 bg-gradient-to-b from-red-50 to-orange-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 rounded-full text-red-600 text-sm font-bold mb-4">
                    <span>🎪</span> 精選推薦 <span>🎪</span>
                </div>
                <h2 class="text-4xl sm:text-5xl font-black text-gray-800 mb-4">
                    近期<span class="text-festive-gradient">精彩</span>活動
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    為您推薦即將舉辦的熱門活動，名額有限，手快有手慢無！
                </p>
            </div>

            @if($featuredActivities->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($featuredActivities as $index => $activity)
                        <x-activity-card :activity="$activity" :delay="$index * 100" />
                    @endforeach
                </div>

                <div class="text-center mt-16">
                    <a href="{{ route('activities.index') }}" class="group inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white font-bold text-lg rounded-full shadow-xl shadow-red-500/30 hover:shadow-red-500/50 hover:scale-105 transition-all duration-300">
                        <span>查看更多活動</span>
                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                    </a>
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-red-200 shadow-xl">
                    <div class="text-6xl mb-6 animate-bounce">🎭</div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">暫無活動</h3>
                    <p class="text-gray-500 mb-6">目前沒有即將舉辦的活動，請稍後再來查看</p>
                    <div class="flex justify-center gap-2 text-3xl">
                        <span class="animate-wave">🏮</span>
                        <span class="animate-wave delay-100">🏮</span>
                        <span class="animate-wave delay-200">🏮</span>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 sm:py-28 bg-white relative overflow-hidden">
        <!-- Decorative background -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-bl from-red-100 to-transparent rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-orange-100 to-transparent rounded-full blur-3xl opacity-50"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100 rounded-full text-amber-700 text-sm font-bold mb-4">
                    <span>⭐</span> 我們的優勢 <span>⭐</span>
                </div>
                <h2 class="text-4xl sm:text-5xl font-black text-gray-800 mb-4">
                    為什麼選擇<span class="text-festive-gradient">我們</span>
                </h2>
                <p class="text-lg text-gray-600">
                    簡單、快速、可靠的活動預約體驗
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group text-center p-8 rounded-3xl bg-gradient-to-br from-red-50 to-orange-50 border-2 border-red-100 hover:border-red-300 hover:shadow-2xl hover:shadow-red-500/10 transition-all duration-500 hover:-translate-y-2">
                    <div class="w-20 h-20 bg-gradient-to-br from-red-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-red-500/30 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                        <span class="text-4xl">⚡</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">快速預約</h3>
                    <p class="text-gray-600">
                        一鍵完成預約，無需繁瑣流程，節省您的寶貴時間
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="group text-center p-8 rounded-3xl bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-100 hover:border-emerald-300 hover:shadow-2xl hover:shadow-emerald-500/10 transition-all duration-500 hover:-translate-y-2">
                    <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-emerald-500/30 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                        <span class="text-4xl">🛡️</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">安全可靠</h3>
                    <p class="text-gray-600">
                        完善的資料保護機制，確保您的個人資訊安全無虞
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="group text-center p-8 rounded-3xl bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-100 hover:border-amber-300 hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-500 hover:-translate-y-2">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-amber-500/30 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                        <span class="text-4xl">🔔</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">即時更新</h3>
                    <p class="text-gray-600">
                        活動資訊即時同步，掌握最新動態與剩餘名額
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    @guest
        <section class="py-20">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-red-600 via-red-500 to-orange-500 p-10 sm:p-16 text-center shadow-2xl shadow-red-500/30">
                    <!-- Decorative elements -->
                    <div class="absolute top-5 left-10 text-5xl animate-wave opacity-80">🏮</div>
                    <div class="absolute top-10 right-10 text-4xl animate-wave delay-200 opacity-80">🏮</div>
                    <div class="absolute bottom-10 left-20 text-3xl animate-float opacity-60">✨</div>
                    <div class="absolute bottom-5 right-20 text-3xl animate-float delay-300 opacity-60">✨</div>

                    <div class="relative">
                        <h2 class="text-4xl sm:text-5xl font-black text-white mb-4">
                            準備好開始了嗎？
                        </h2>
                        <p class="text-lg text-white/90 mb-10 max-w-xl mx-auto">
                            立即註冊帳號，開始探索並預約您感興趣的活動！<br>
                            精彩活動等著您！
                        </p>
                        <a href="{{ route('register') }}" class="group inline-flex items-center gap-3 px-10 py-5 bg-white text-red-600 font-black text-xl rounded-full shadow-2xl hover:shadow-white/30 hover:scale-105 transition-all duration-300">
                            <span class="group-hover:rotate-12 transition-transform">🎁</span>
                            免費註冊
                            <span class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endguest
</x-layouts.app>
