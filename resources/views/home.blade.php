{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

@extends('layouts.app')

@section('title', '活动预约平台')

@section('content')
    {{-- Hero Section --}}
    <section class="relative min-h-[600px] flex items-center justify-center bg-slate-900 overflow-hidden">
        {{-- Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-primary-900 via-slate-900 to-accent-900"></div>
        <div class="absolute inset-0 bg-pattern opacity-10"></div>

        {{-- Animated Orbs --}}
        <div class="absolute top-20 left-20 w-72 h-72 bg-primary-500/30 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-accent-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6">
                发现精彩
                <span class="bg-gradient-to-r from-primary-400 to-accent-400 bg-clip-text text-transparent">活动体验</span>
            </h1>
            <p class="text-xl md:text-2xl text-slate-300 max-w-2xl mx-auto mb-10">
                探索各种精彩活动，轻松预约，开启你的下一段精彩旅程
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('activities.index') }}" class="btn-gradient text-white px-8 py-4 rounded-xl font-bold text-lg inline-flex items-center justify-center group">
                    浏览活动
                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                @guest
                    <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl font-bold text-lg border-2 border-white/30 text-white hover:bg-white/10 transition-colors inline-flex items-center justify-center">
                        立即注册
                    </a>
                @endguest
            </div>
        </div>

        {{-- Scroll Indicator --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </div>
    </section>

    {{-- Featured Activities --}}
    @if($featuredActivities->count() > 0)
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-semibold mb-4">精选推荐</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">热门活动</h2>
                    <p class="text-slate-600 max-w-xl mx-auto">探索我们精心挑选的热门活动，不要错过这些精彩体验</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($featuredActivities as $activity)
                        @include('components.activity-card', ['activity' => $activity])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Upcoming Activities --}}
    @if($upcomingActivities->count() > 0)
        <section class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-12">
                    <div>
                        <span class="inline-block px-4 py-2 bg-accent-100 text-accent-600 rounded-full text-sm font-semibold mb-4">即将开始</span>
                        <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">近期活动</h2>
                        <p class="text-slate-600">查看即将举办的精彩活动</p>
                    </div>
                    <a href="{{ route('activities.index') }}" class="mt-6 md:mt-0 inline-flex items-center text-primary-600 font-semibold hover:text-primary-700 group">
                        查看全部活动
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($upcomingActivities as $activity)
                        @include('components.activity-card-compact', ['activity' => $activity])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Features Section --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">为什么选择我们</h2>
                <p class="text-slate-600 max-w-xl mx-auto">我们致力于为您提供最佳的活动预约体验</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="text-center p-8 rounded-2xl bg-gradient-to-br from-primary-50 to-white border border-primary-100 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">快速预约</h3>
                    <p class="text-slate-600">简单几步即可完成活动预约，节省您的宝贵时间</p>
                </div>

                {{-- Feature 2 --}}
                <div class="text-center p-8 rounded-2xl bg-gradient-to-br from-accent-50 to-white border border-accent-100 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-accent-500 to-accent-600 rounded-2xl flex items-center justify-center shadow-lg shadow-accent-500/30 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">安全可靠</h3>
                    <p class="text-slate-600">完善的信息保护机制，让您的预约安心无忧</p>
                </div>

                {{-- Feature 3 --}}
                <div class="text-center p-8 rounded-2xl bg-gradient-to-br from-emerald-50 to-white border border-emerald-100 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">实时更新</h3>
                    <p class="text-slate-600">活动状态实时更新，随时掌握最新动态</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 bg-gradient-to-r from-primary-600 to-accent-600 relative overflow-hidden">
        <div class="absolute inset-0 bg-pattern opacity-10"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">准备好开始你的活动之旅了吗？</h2>
            <p class="text-xl text-white/80 mb-10">立即注册，探索无限可能</p>
            @guest
                <a href="{{ route('register') }}" class="inline-block bg-white text-primary-600 px-10 py-4 rounded-xl font-bold text-lg hover:bg-slate-50 transition-colors shadow-lg shadow-black/20">
                    免费注册
                </a>
            @else
                <a href="{{ route('activities.index') }}" class="inline-block bg-white text-primary-600 px-10 py-4 rounded-xl font-bold text-lg hover:bg-slate-50 transition-colors shadow-lg shadow-black/20">
                    开始探索
                </a>
            @endguest
        </div>
    </section>
@endsection
