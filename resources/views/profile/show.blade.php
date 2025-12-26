@extends('layouts.app')

@section('title', '个人中心')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">个人中心</h1>
        <p class="mt-2 text-gray-600">管理您的个人资料</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Avatar -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">头像</label>
                <div class="flex items-center space-x-4">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">
                    @else
                        <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png" 
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-lg file:border-0
                                      file:text-sm file:font-medium
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">JPG 或 PNG，最大 2MB</p>
                    </div>
                </div>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">姓名</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Email (Read-only) -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">邮箱</label>
                <input type="email" id="email" value="{{ $user->email }}" disabled
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-500">邮箱地址不可修改</p>
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">手机号</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="请输入手机号">
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('profile.bookings') }}" class="text-gray-600 hover:text-gray-900">
                    查看我的预约
                </a>
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg transition">
                    保存更改
                </button>
            </div>
        </form>
    </div>

    <!-- Account Info -->
    <div class="mt-6 bg-gray-50 rounded-lg border border-gray-200 p-4">
        <div class="text-sm text-gray-600">
            <div class="flex justify-between py-2">
                <span>账号创建时间:</span>
                <span class="font-medium text-gray-900">{{ $user->created_at->format('Y-m-d H:i') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

