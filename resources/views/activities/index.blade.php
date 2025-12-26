{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<x-layouts.app>
    <x-slot:title>全部活動</x-slot:title>

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-red-600 via-red-500 to-orange-500 py-16 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-5 left-[10%] text-4xl animate-wave opacity-60">🏮</div>
            <div class="absolute top-10 right-[15%] text-3xl animate-wave delay-200 opacity-60">🏮</div>
            <div class="absolute bottom-5 left-[30%] text-2xl animate-float opacity-40">✨</div>
            <div class="absolute bottom-10 right-[25%] text-2xl animate-float delay-100 opacity-40">✨</div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center animate-slide-up">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-medium mb-4">
                    <span>📅</span> 活動列表 <span>📅</span>
                </div>
                <h1 class="text-4xl sm:text-5xl font-black text-white mb-3">
                    全部活動
                </h1>
                <p class="text-lg text-white/90 max-w-xl mx-auto">
                    探索所有即將舉辦的精彩活動，找到屬於你的精彩時刻！
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Activities Grid -->
        @if($activities->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($activities as $index => $activity)
                    <x-activity-card :activity="$activity" :delay="$index * 80" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12 flex justify-center">
                {{ $activities->links() }}
            </div>
        @else
            <div class="text-center py-24 bg-white rounded-3xl border-2 border-dashed border-red-200 shadow-xl">
                <div class="text-7xl mb-6 animate-bounce">🎭</div>
                <h3 class="text-2xl font-bold text-gray-700 mb-3">暫無活動</h3>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">目前沒有可預約的活動，請稍後再來查看，精彩活動即將上線！</p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-500 to-orange-500 text-white font-bold rounded-full hover:scale-105 transition-all shadow-lg shadow-red-500/30">
                    <span>🏠</span> 返回首頁
                </a>
                <div class="flex justify-center gap-3 text-4xl mt-10">
                    <span class="animate-wave">🏮</span>
                    <span class="animate-wave delay-100">🧧</span>
                    <span class="animate-wave delay-200">🏮</span>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
