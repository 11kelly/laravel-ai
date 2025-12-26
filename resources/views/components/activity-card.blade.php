{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<article class="group bg-white rounded-3xl overflow-hidden shadow-lg card-hover border border-slate-100">
    <a href="{{ route('activities.show', $activity->slug) }}" class="block">
        {{-- Image --}}
        <div class="relative h-56 overflow-hidden">
            @if($activity->cover_image)
                <img src="{{ asset('storage/' . $activity->cover_image) }}"
                     alt="{{ $activity->title }}"
                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            @else
                <div class="w-full h-full bg-gradient-to-br from-primary-400 to-accent-400 flex items-center justify-center">
                    <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif

            {{-- Status Badge --}}
            <div class="absolute top-4 left-4">
                @if($activity->is_featured)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-amber-400 to-orange-500 text-white shadow-lg">
                        ⭐ 推荐
                    </span>
                @endif
            </div>

            {{-- Availability Badge --}}
            <div class="absolute top-4 right-4">
                @if($activity->end_time < now())
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-800/80 text-white backdrop-blur-sm">
                        已结束
                    </span>
                @elseif($activity->booked_count >= $activity->capacity)
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/90 text-white backdrop-blur-sm">
                        已满
                    </span>
                @elseif($activity->remaining_capacity <= 5)
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/90 text-white backdrop-blur-sm">
                        仅剩 {{ $activity->remaining_capacity }} 位
                    </span>
                @endif
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6">
            <h3 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-primary-600 transition-colors line-clamp-2">
                {{ $activity->title }}
            </h3>

            <div class="space-y-2 mb-4">
                <div class="flex items-center text-slate-500 text-sm">
                    <svg class="w-4 h-4 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $activity->start_time->format('Y年m月d日 H:i') }}</span>
                </div>
                <div class="flex items-center text-slate-500 text-sm">
                    <svg class="w-4 h-4 mr-2 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="truncate">{{ $activity->location }}</span>
                </div>
            </div>

            @if($activity->description)
                <p class="text-slate-600 text-sm line-clamp-2 mb-4">{{ $activity->description }}</p>
            @endif

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <div class="flex items-center text-sm text-slate-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ $activity->booked_count }}/{{ $activity->capacity }}</span>
                </div>
                <span class="text-primary-600 font-semibold text-sm group-hover:underline">
                    查看详情 →
                </span>
            </div>
        </div>
    </a>
</article>

