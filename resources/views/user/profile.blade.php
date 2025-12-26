{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

@extends('layouts.app')

@section('title', '个人中心')

@section('content')
    <div class="min-h-screen bg-slate-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-slate-900">个人中心</h1>
                <p class="text-slate-600 mt-2">管理您的账户信息和预约</p>
            </div>

            {{-- Navigation Tabs --}}
            <div class="bg-white rounded-2xl shadow-sm mb-8">
                <nav class="flex border-b border-slate-200">
                    <a href="{{ route('user.profile') }}"
                       class="flex-1 py-4 px-6 text-center font-semibold border-b-2 border-primary-500 text-primary-600">
                        个人资料
                    </a>
                    <a href="{{ route('user.bookings') }}"
                       class="flex-1 py-4 px-6 text-center font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors">
                        我的预约
                    </a>
                </nav>
            </div>

            {{-- Profile Form --}}
            <div class="bg-white rounded-2xl shadow-sm p-8">
                {{-- Avatar Section --}}
                <div class="flex items-center mb-8 pb-8 border-b border-slate-200">
                    <div class="relative">
                        <img src="{{ auth()->user()->avatar_url }}"
                             alt="{{ auth()->user()->name }}"
                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                        <button class="absolute bottom-0 right-0 w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white shadow-lg hover:bg-primary-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="ml-6">
                        <h2 class="text-2xl font-bold text-slate-900">{{ auth()->user()->name }}</h2>
                        <p class="text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-2">姓名</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', auth()->user()->name) }}"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                   required>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-2">电子邮箱</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', auth()->user()->email) }}"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                   required>
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">手机号码</label>
                            <input type="tel"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone', auth()->user()->phone) }}"
                                   placeholder="请输入手机号码"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                        </div>

                        {{-- Avatar --}}
                        <div>
                            <label for="avatar" class="block text-sm font-medium text-slate-700 mb-2">头像</label>
                            <input type="file"
                                   id="avatar"
                                   name="avatar"
                                   accept="image/*"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-50 file:text-primary-600 file:font-semibold hover:file:bg-primary-100">
                        </div>
                    </div>

                    <hr class="my-8 border-slate-200">

                    {{-- Password Section --}}
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">修改密码</h3>
                    <p class="text-sm text-slate-500 mb-6">如需修改密码，请填写以下字段（否则留空）</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Current Password --}}
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-slate-700 mb-2">当前密码</label>
                            <input type="password"
                                   id="current_password"
                                   name="current_password"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                   placeholder="输入当前密码">
                        </div>

                        <div></div>

                        {{-- New Password --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-2">新密码</label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                   placeholder="输入新密码">
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">确认新密码</label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                                   placeholder="再次输入新密码">
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="btn-gradient text-white px-8 py-3 rounded-xl font-semibold">
                            保存更改
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

