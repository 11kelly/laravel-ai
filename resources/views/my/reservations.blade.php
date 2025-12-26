{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<x-layouts.app>
    <x-slot:title>我的預約</x-slot:title>

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-red-600 via-red-500 to-orange-500 py-12 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-3 left-[15%] text-3xl animate-wave opacity-60">🏮</div>
            <div class="absolute top-5 right-[10%] text-2xl animate-wave delay-200 opacity-60">🏮</div>
        </div>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center animate-slide-up">
                <h1 class="text-3xl sm:text-4xl font-black text-white mb-2">
                    🎫 我的預約
                </h1>
                <p class="text-white/90">管理您的活動預約記錄</p>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Tabs -->
        <div class="flex gap-2 mb-8 bg-white rounded-2xl p-2 shadow-lg border border-red-100">
            <a href="{{ route('my.reservations') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white shadow-md">
                <span>🎫</span> 我的預約
            </a>
            <a href="{{ route('my.profile') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 hover:bg-red-50 transition-colors">
                <span>👤</span> 個人資料
            </a>
        </div>

        @if($reservations->count() > 0)
            <div class="space-y-5">
                @foreach($reservations as $index => $reservation)
                    <div class="bg-white rounded-3xl border-2 border-red-100 p-6 shadow-lg hover:shadow-xl hover:border-red-200 transition-all duration-300 opacity-0 animate-slide-up" style="animation-delay: {{ $index * 100 }}ms; animation-fill-mode: forwards;">
                        <div class="flex flex-col sm:flex-row gap-6">
                            <!-- Activity Image -->
                            <div class="w-full sm:w-44 h-32 rounded-2xl overflow-hidden bg-gradient-to-br from-red-100 via-orange-100 to-yellow-100 shrink-0 shadow-md">
                                @if($reservation->activity->cover_image)
                                    <img src="{{ $reservation->activity->cover_image_url }}" alt="{{ $reservation->activity->title }}" class="w-full h-full object-cover hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <span class="text-4xl">🎪</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                    <div>
                                        <a href="{{ route('activities.show', $reservation->activity) }}" class="text-xl font-bold text-gray-800 hover:text-red-600 transition-colors line-clamp-1">
                                            {{ $reservation->activity->title }}
                                        </a>

                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3 text-sm text-gray-500">
                                            <span class="flex items-center gap-1.5 bg-red-50 px-3 py-1 rounded-full">
                                                <span>📅</span>
                                                {{ $reservation->activity->start_time->format('Y/m/d H:i') }}
                                            </span>
                                            <span class="flex items-center gap-1.5 bg-amber-50 px-3 py-1 rounded-full">
                                                <span>⏰</span>
                                                預約於 {{ $reservation->reserved_at->format('m/d H:i') }}
                                            </span>
                                        </div>

                                        @if($reservation->remark)
                                            <p class="mt-3 text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded-lg">💬 {{ $reservation->remark }}</p>
                                        @endif
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="shrink-0">
                                        @php
                                            $statusConfig = [
                                                'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-300', 'icon' => '⏳'],
                                                'confirmed' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-300', 'icon' => '✅'],
                                                'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-300', 'icon' => '❌'],
                                                'expired' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'border' => 'border-gray-300', 'icon' => '⏰'],
                                            ];
                                            $config = $statusConfig[$reservation->status] ?? $statusConfig['expired'];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-4 py-2 text-sm font-bold rounded-full border-2 {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                                            <span>{{ $config['icon'] }}</span>
                                            {{ $reservation->status_label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-4 mt-5 pt-5 border-t-2 border-dashed border-red-100">
                                    <a href="{{ route('activities.show', $reservation->activity) }}" class="text-sm text-red-600 hover:text-red-700 font-bold flex items-center gap-1 hover:gap-2 transition-all">
                                        <span>👀</span> 查看活動
                                    </a>

                                    @if($reservation->canBeCancelled())
                                        <span class="text-red-200">|</span>
                                        <form action="{{ route('my.reservations.cancel', $reservation) }}" method="POST" x-data="{ confirming: false }">
                                            @csrf
                                            <button type="button" @click="confirming = true" x-show="!confirming" class="text-sm text-gray-500 hover:text-red-600 font-medium flex items-center gap-1 transition-colors">
                                                <span>🗑️</span> 取消預約
                                            </button>
                                            <div x-show="confirming" class="flex items-center gap-3 bg-red-50 px-3 py-1.5 rounded-full">
                                                <span class="text-sm text-red-600 font-medium">確定取消？</span>
                                                <button type="submit" class="text-sm text-white bg-red-500 hover:bg-red-600 px-3 py-1 rounded-full font-bold transition-colors">確定</button>
                                                <button type="button" @click="confirming = false" class="text-sm text-gray-500 hover:text-gray-700 font-medium">返回</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $reservations->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-red-200 shadow-xl">
                <div class="text-7xl mb-6 animate-bounce">🎫</div>
                <h3 class="text-2xl font-bold text-gray-700 mb-3">尚無預約記錄</h3>
                <p class="text-gray-500 mb-8">快去瀏覽活動並預約吧！精彩活動等著您！</p>
                <a href="{{ route('activities.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white font-bold text-lg rounded-full hover:scale-105 transition-all shadow-xl shadow-red-500/30">
                    <span>🎪</span> 瀏覽活動
                    <span>→</span>
                </a>
                <div class="flex justify-center gap-3 text-4xl mt-10">
                    <span class="animate-wave">🏮</span>
                    <span class="animate-wave delay-100">🎊</span>
                    <span class="animate-wave delay-200">🏮</span>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
