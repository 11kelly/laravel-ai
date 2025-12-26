{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<article class="group bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 border border-slate-100">
    <a href="{{ route('activities.show', $activity->slug) }}" class="block">
        {{-- Image --}}
        <div class="relative h-40 overflow-hidden">
            @if($activity->cover_image)
                <img src="{{ asset('storage/' . $activity->cover_image) }}"
                     alt="{{ $activity->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            @else
                <div class="w-full h-full bg-gradient-to-br from-primary-400 to-accent-400 flex items-center justify-center">
                    <svg class="w-10 h-10 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif

            {{-- Date Badge --}}
            <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm rounded-xl px-3 py-2 text-center shadow-md">
                <div class="text-xs text-slate-500 uppercase font-medium">{{ $activity->start_time->format('M') }}</div>
                <div class="text-2xl font-bold text-slate-900 leading-none">{{ $activity->start_time->format('d') }}</div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-4">
            <h3 class="font-bold text-slate-900 mb-2 group-hover:text-primary-600 transition-colors line-clamp-1">
                {{ $activity->title }}
            </h3>

            <div class="flex items-center text-slate-500 text-xs mb-3">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                <span class="truncate">{{ $activity->location }}</span>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-full bg-slate-200 rounded-full h-1.5 w-20 mr-2">
                        <div class="bg-primary-500 h-1.5 rounded-full" style="width: {{ min(100, ($activity->booked_count / $activity->capacity) * 100) }}%"></div>
                    </div>
                    <span class="text-xs text-slate-500">{{ $activity->remaining_capacity }}位</span>
                </div>
            </div>
        </div>
    </a>
</article>

