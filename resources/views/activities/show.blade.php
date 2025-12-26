{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<x-layouts.app>
    <x-slot:title>{{ $activity->title }}</x-slot:title>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Breadcrumb -->
        <nav class="mb-8 animate-slide-in-left">
            <ol class="flex items-center gap-2 text-sm text-gray-500">
                <li><a href="{{ route('home') }}" class="hover:text-red-600 transition-colors flex items-center gap-1"><span>🏠</span> 首頁</a></li>
                <li class="text-red-300">›</li>
                <li><a href="{{ route('activities.index') }}" class="hover:text-red-600 transition-colors flex items-center gap-1"><span>📅</span> 活動</a></li>
                <li class="text-red-300">›</li>
                <li class="text-red-600 font-medium truncate max-w-[200px]">{{ $activity->title }}</li>
            </ol>
        </nav>

        <div class="bg-white rounded-[2rem] shadow-2xl shadow-red-500/10 overflow-hidden border-2 border-red-100 animate-bounce-in">
            <!-- Cover Image -->
            <div class="aspect-[21/9] relative bg-gradient-to-br from-red-100 via-orange-100 to-yellow-100 overflow-hidden">
                @if($activity->cover_image)
                    <img src="{{ $activity->cover_image_url }}" alt="{{ $activity->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <span class="text-8xl animate-float">🎪</span>
                    </div>
                @endif

                <!-- Decorative corners -->
                <div class="absolute top-4 left-4 text-3xl animate-wave">🏮</div>
                <div class="absolute top-4 right-4 text-3xl animate-wave delay-200">🏮</div>

                <!-- Status Overlay -->
                @if($activity->hasEnded())
                    <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm flex items-center justify-center">
                        <div class="text-center">
                            <span class="text-6xl mb-4 block">⏰</span>
                            <span class="px-8 py-3 bg-gray-800 text-white text-xl font-bold rounded-full">活動已結束</span>
                        </div>
                    </div>
                @elseif($remainingCapacity === 0)
                    <div class="absolute inset-0 bg-red-900/70 backdrop-blur-sm flex items-center justify-center">
                        <div class="text-center">
                            <span class="text-6xl mb-4 block">🔥</span>
                            <span class="px-8 py-3 bg-red-600 text-white text-xl font-bold rounded-full">名額已滿</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Content -->
            <div class="p-8 sm:p-12">
                <div class="flex flex-col lg:flex-row gap-12">
                    <!-- Main Content -->
                    <div class="flex-1">
                        <h1 class="text-3xl sm:text-4xl font-black text-gray-800 mb-8 leading-tight">
                            {{ $activity->title }}
                        </h1>

                        <!-- Meta Info Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-10">
                            <div class="flex items-center gap-4 p-5 bg-gradient-to-br from-red-50 to-orange-50 rounded-2xl border border-red-100 group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                                <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/30 group-hover:scale-110 transition-transform">
                                    <span class="text-2xl">📅</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">活動時間</p>
                                    <p class="text-base font-bold text-gray-800">{{ $activity->start_time->format('Y/m/d H:i') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-5 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl border border-emerald-100 group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                                    <span class="text-2xl">⏰</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">結束時間</p>
                                    <p class="text-base font-bold text-gray-800">{{ $activity->end_time->format('Y/m/d H:i') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-5 bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl border border-purple-100 group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                                    <span class="text-2xl">👥</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">名額狀態</p>
                                    @if($activity->capacity === 0)
                                        <p class="text-base font-bold text-gray-800">名額不限 🎊</p>
                                    @else
                                        <p class="text-base font-bold text-gray-800">
                                            剩餘 <span class="text-red-600">{{ $remainingCapacity }}</span> / {{ $activity->capacity }} 名
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-5 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-2xl border border-amber-100 group hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                                <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                                    <span class="text-2xl">🎫</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">已預約人數</p>
                                    <p class="text-base font-bold text-gray-800">{{ $activity->reserved_count }} 人</p>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($activity->description)
                            <div class="prose prose-slate max-w-none">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-2xl">📝</span>
                                    <h3 class="text-xl font-bold text-gray-800 m-0">活動說明</h3>
                                </div>
                                <div class="text-gray-600 leading-relaxed bg-gray-50 rounded-2xl p-6 border border-gray-100 prose prose-sm prose-red max-w-none">
                                    {!! $activity->safe_description !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:w-80 shrink-0">
                        <div class="sticky top-24 p-6 bg-gradient-to-br from-red-50 via-orange-50 to-yellow-50 rounded-3xl border-2 border-red-200 shadow-xl">
                            <!-- Decorative header -->
                            <div class="flex justify-center gap-2 mb-4">
                                <span class="text-2xl animate-wave">🏮</span>
                                <span class="text-2xl animate-wave delay-100">🧧</span>
                                <span class="text-2xl animate-wave delay-200">🏮</span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-800 mb-6 text-center">預約資訊</h3>

                            @if($hasReserved)
                                <div class="p-5 bg-gradient-to-r from-emerald-100 to-teal-100 border-2 border-emerald-300 rounded-2xl mb-4">
                                    <div class="flex items-center justify-center gap-2 text-emerald-700">
                                        <span class="text-2xl">✅</span>
                                        <span class="font-bold text-lg">您已預約此活動</span>
                                    </div>
                                </div>
                                <a href="{{ route('my.reservations') }}" class="block w-full py-4 px-4 text-center bg-gradient-to-r from-red-500 to-orange-500 text-white font-bold rounded-2xl hover:scale-105 transition-all shadow-lg shadow-red-500/30">
                                    🎫 查看我的預約
                                </a>
                            @elseif($activity->isReservable())
                                <form action="{{ route('activities.reserve', $activity) }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                    @csrf
                                    <div class="mb-5">
                                        <label for="remark" class="block text-sm font-bold text-gray-700 mb-2">📝 備註（選填）</label>
                                        <textarea name="remark" id="remark" rows="3" class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none bg-white/80" placeholder="有任何特殊需求請在此說明..."></textarea>
                                    </div>

                                    @auth
                                        <button type="submit" :disabled="submitting" class="w-full py-4 px-4 bg-gradient-to-r from-red-500 via-red-600 to-orange-500 text-white font-bold text-lg rounded-2xl shadow-xl shadow-red-500/30 hover:shadow-red-500/50 hover:scale-105 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 relative overflow-hidden">
                                            <span x-show="!submitting" class="flex items-center justify-center gap-2">
                                                <span class="text-xl">🎯</span> 立即預約
                                            </span>
                                            <span x-show="submitting" class="flex items-center justify-center gap-2">
                                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                處理中...
                                            </span>
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}" class="block w-full py-4 px-4 text-center bg-gradient-to-r from-red-500 to-orange-500 text-white font-bold text-lg rounded-2xl shadow-xl shadow-red-500/30 hover:shadow-red-500/50 hover:scale-105 transition-all">
                                            🔐 登入後預約
                                        </a>
                                        <p class="text-center text-sm text-gray-500 mt-4">
                                            還沒有帳號？<a href="{{ route('register') }}" class="text-red-600 hover:underline font-bold">立即註冊</a>
                                        </p>
                                    @endauth
                                </form>
                            @elseif($activity->hasEnded())
                                <div class="p-5 bg-gray-100 rounded-2xl text-center">
                                    <span class="text-3xl block mb-2">⏰</span>
                                    <p class="text-gray-500 font-medium">此活動已結束</p>
                                </div>
                            @elseif($remainingCapacity === 0)
                                <div class="p-5 bg-red-100 border-2 border-red-300 rounded-2xl text-center">
                                    <span class="text-3xl block mb-2">🔥</span>
                                    <p class="text-red-600 font-bold">名額已滿</p>
                                </div>
                            @else
                                <div class="p-5 bg-gray-100 rounded-2xl text-center">
                                    <span class="text-3xl block mb-2">🚧</span>
                                    <p class="text-gray-500">此活動暫不開放預約</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mt-10">
            <a href="{{ route('activities.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-red-600 transition-colors font-medium group">
                <span class="group-hover:-translate-x-1 transition-transform">←</span>
                <span>📅</span> 返回活動列表
            </a>
        </div>
    </div>
</x-layouts.app>
