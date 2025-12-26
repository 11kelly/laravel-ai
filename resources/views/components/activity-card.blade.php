{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

@props(['activity', 'delay' => 0])

<a href="{{ route('activities.show', $activity) }}" 
   class="group block bg-white rounded-3xl shadow-lg border-2 border-red-100 overflow-hidden festive-card opacity-0 animate-bounce-in"
   style="animation-delay: {{ $delay }}ms; animation-fill-mode: forwards;">
    
    <!-- Cover Image -->
    <div class="aspect-[16/10] relative overflow-hidden bg-gradient-to-br from-red-100 via-orange-100 to-yellow-100">
        @if($activity->cover_image)
            <img src="{{ $activity->cover_image_url }}" alt="{{ $activity->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <span class="text-6xl group-hover:scale-125 group-hover:rotate-12 transition-transform duration-500">🎪</span>
            </div>
        @endif

        <!-- Decorative ribbon -->
        <div class="absolute -right-12 top-6 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-bold py-1 px-12 rotate-45 shadow-lg">
            熱門
        </div>

        <!-- Status Badge -->
        @if($activity->hasEnded())
            <div class="absolute top-3 left-3 px-4 py-1.5 bg-gray-800/90 backdrop-blur-sm text-white text-xs font-bold rounded-full flex items-center gap-1">
                <span>⏰</span> 已結束
            </div>
        @elseif($activity->getRemainingCapacity() === 0)
            <div class="absolute top-3 left-3 px-4 py-1.5 bg-red-600/90 backdrop-blur-sm text-white text-xs font-bold rounded-full flex items-center gap-1 animate-pulse">
                <span>🔥</span> 已額滿
            </div>
        @elseif($activity->start_time->isToday())
            <div class="absolute top-3 left-3 px-4 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold rounded-full flex items-center gap-1 animate-pulse-glow">
                <span>⚡</span> 今日活動
            </div>
        @else
            <div class="absolute top-3 left-3 px-4 py-1.5 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs font-bold rounded-full flex items-center gap-1">
                <span>✨</span> 開放預約
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-5 relative">
        <!-- Decorative corner -->
        <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-red-50 to-transparent rounded-bl-3xl"></div>

        <h3 class="text-lg font-bold text-gray-800 group-hover:text-red-600 transition-colors duration-300 line-clamp-2 mb-4 relative z-10">
            {{ $activity->title }}
        </h3>

        <div class="space-y-2.5 text-sm">
            <!-- Date -->
            <div class="flex items-center gap-3 text-gray-600">
                <div class="w-8 h-8 bg-gradient-to-br from-red-100 to-orange-100 rounded-lg flex items-center justify-center">
                    <span class="text-base">📅</span>
                </div>
                <span>{{ $activity->start_time->format('Y/m/d H:i') }}</span>
            </div>

            <!-- Capacity -->
            <div class="flex items-center gap-3 text-gray-600">
                <div class="w-8 h-8 bg-gradient-to-br from-amber-100 to-yellow-100 rounded-lg flex items-center justify-center">
                    <span class="text-base">👥</span>
                </div>
                @if($activity->capacity === 0)
                    <span>名額不限 🎊</span>
                @else
                    <span>剩餘 <strong class="text-red-600">{{ $activity->getRemainingCapacity() }}</strong> / {{ $activity->capacity }} 名</span>
                @endif
            </div>
        </div>

        <!-- CTA -->
        <div class="mt-5 pt-4 border-t-2 border-dashed border-red-100">
            @if($activity->isReservable())
                <span class="inline-flex items-center gap-2 text-sm font-bold text-red-600 group-hover:text-red-700">
                    <span class="group-hover:animate-bounce">🎯</span>
                    立即預約
                    <svg class="w-4 h-4 group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </span>
            @else
                <span class="text-sm text-gray-400 flex items-center gap-2">
                    <span>👀</span> 查看詳情
                </span>
            @endif
        </div>
    </div>
</a>
