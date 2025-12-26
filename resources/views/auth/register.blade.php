@extends('layouts.app')

@section('title', '注册 - 活动预约系统')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 border border-gray-200 rounded-xl shadow-sm">
    <h2 class="text-2xl font-bold mb-6 text-center">创建账号</h2>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">姓名</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-2.5 px-4 transition-all duration-200" placeholder="您的姓名">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">邮箱</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-2.5 px-4 transition-all duration-200" placeholder="your@email.com">
            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">密码</label>
            <input type="password" name="password" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-2.5 px-4 transition-all duration-200" placeholder="••••••••">
            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">确认密码</label>
            <input type="password" name="password_confirmation" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-2.5 px-4 transition-all duration-200" placeholder="••••••••">
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-indigo-700 active:transform active:scale-[0.98] transition-all shadow-sm">注册</button>
    </form>
    <div class="mt-4 text-center text-sm text-gray-600">
        已有账号？ <a href="{{ route('login') }}" class="text-indigo-600 font-medium">立即登录</a>
    </div>
</div>
@endsection

