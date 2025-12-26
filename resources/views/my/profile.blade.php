{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

<x-layouts.app>
    <x-slot:title>個人資料</x-slot:title>

    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-red-600 via-red-500 to-orange-500 py-12 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-3 left-[15%] text-3xl animate-wave opacity-60">🏮</div>
            <div class="absolute top-5 right-[10%] text-2xl animate-wave delay-200 opacity-60">🏮</div>
        </div>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center animate-slide-up">
                <h1 class="text-3xl sm:text-4xl font-black text-white mb-2">
                    👤 個人資料
                </h1>
                <p class="text-white/90">管理您的帳戶資訊</p>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <!-- Tabs -->
        <div class="flex gap-2 mb-8 bg-white rounded-2xl p-2 shadow-lg border border-red-100">
            <a href="{{ route('my.reservations') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium rounded-xl text-gray-600 hover:bg-red-50 transition-colors">
                <span>🎫</span> 我的預約
            </a>
            <a href="{{ route('my.profile') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white shadow-md">
                <span>👤</span> 個人資料
            </a>
        </div>

        <div class="space-y-8">
            <!-- Profile Form -->
            <div class="bg-white rounded-3xl border-2 border-red-100 p-8 shadow-xl animate-slide-up">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/30">
                        <span class="text-2xl">📝</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">基本資料</h2>
                </div>

                <form action="{{ route('my.profile.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2">📧 Email</label>
                        <input type="email" id="email" value="{{ $user->email }}" disabled class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-200 rounded-xl text-gray-500 cursor-not-allowed">
                        <p class="mt-2 text-sm text-gray-500 flex items-center gap-1">🔒 Email 無法變更</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">👤 姓名 <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">📱 電話</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('phone') border-red-500 @enderror" placeholder="例：0912345678">
                        @error('phone')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-red-500 to-orange-500 text-white font-bold rounded-xl hover:scale-105 transition-all shadow-lg shadow-red-500/30 flex items-center gap-2">
                            <span>💾</span> 儲存變更
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Form -->
            <div class="bg-white rounded-3xl border-2 border-red-100 p-8 shadow-xl animate-slide-up delay-100" style="animation-delay: 0.1s; animation-fill-mode: forwards; opacity: 0;">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-yellow-500 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/30">
                        <span class="text-2xl">🔐</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">變更密碼</h2>
                </div>

                <form action="{{ route('my.password.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-bold text-gray-700 mb-2">🔑 目前密碼 <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" id="current_password" required class="w-full px-4 py-3 border-2 border-amber-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-700 mb-2">🆕 新密碼 <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="password" required class="w-full px-4 py-3 border-2 border-amber-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">✅ 確認新密碼 <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-3 border-2 border-amber-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-bold rounded-xl hover:scale-105 transition-all shadow-lg shadow-amber-500/30 flex items-center gap-2">
                            <span>🔐</span> 更新密碼
                        </button>
                    </div>
                </form>
            </div>

            <!-- Account Info -->
            <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-3xl border-2 border-red-200 p-6 shadow-lg animate-slide-up delay-200" style="animation-delay: 0.2s; animation-fill-mode: forwards; opacity: 0;">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-xl">ℹ️</span>
                    <h3 class="text-sm font-bold text-gray-700">帳戶資訊</h3>
                </div>
                <dl class="text-sm space-y-3">
                    <div class="flex justify-between items-center bg-white/50 rounded-lg px-4 py-2">
                        <dt class="text-gray-500 flex items-center gap-1"><span>📅</span> 註冊時間</dt>
                        <dd class="text-gray-700 font-medium">{{ $user->created_at->format('Y/m/d H:i') }}</dd>
                    </div>
                    @if($user->email_verified_at)
                        <div class="flex justify-between items-center bg-white/50 rounded-lg px-4 py-2">
                            <dt class="text-gray-500 flex items-center gap-1"><span>✅</span> Email 驗證時間</dt>
                            <dd class="text-gray-700 font-medium">{{ $user->email_verified_at->format('Y/m/d H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-layouts.app>
