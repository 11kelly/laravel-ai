@extends('layouts.app')

@section('title', '个人资料管理 - 活动预约系统')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">个人资料管理</h1>
        <p class="mt-2 text-gray-600">在此更新您的基本信息或更改登录密码。</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('profile.update') }}" method="POST" class="p-8">
            @csrf
            
            <div class="space-y-6">
                <!-- 基本信息 -->
                <div>
                    <h2 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">基本信息</h2>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">姓名</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-3 px-4 transition-all duration-200">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">电子邮箱</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-3 px-4 transition-all duration-200">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 修改密码 -->
                <div class="pt-4">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">修改密码 <span class="text-sm font-normal text-gray-500">(如不修改请留空)</span></h2>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">当前密码</label>
                            <input type="password" name="current_password" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-3 px-4 transition-all duration-200">
                            @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">新密码</label>
                            <input type="password" name="new_password" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-3 px-4 transition-all duration-200">
                            @error('new_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">确认新密码</label>
                            <input type="password" name="new_password_confirmation" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-base py-3 px-4 transition-all duration-200">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6">
                    <button type="submit" class="bg-indigo-600 text-white py-3 px-8 rounded-lg font-bold text-base hover:bg-indigo-700 hover:shadow-lg transform active:scale-[0.98] transition-all duration-200">
                        保存修改
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

