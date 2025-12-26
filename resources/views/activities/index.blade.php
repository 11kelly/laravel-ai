{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

@extends('layouts.app')

@section('title', '活动列表')

@section('content')
    {{-- Header --}}
    <section class="bg-gradient-to-r from-primary-600 to-accent-600 text-white py-16 relative overflow-hidden">
        <div class="absolute inset-0 bg-pattern opacity-10"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">探索活动</h1>
            <p class="text-xl text-white/80">发现各种精彩活动，开启你的体验之旅</p>
        </div>
    </section>

    {{-- Filters --}}
    <section class="bg-white border-b border-slate-200 sticky top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <form method="GET" action="{{ route('activities.index') }}" class="flex flex-col md:flex-row gap-4">
                {{-- Search --}}
                <div class="flex-1">
                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text"
                               name="search"
                               value="{{ $filters['search'] ?? '' }}"
                               placeholder="搜索活动..."
                               class="w-full pl-12 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="w-full md:w-48">
                    <select name="status" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 appearance-none bg-white">
                        <option value="">全部状态</option>
                        <option value="upcoming" {{ ($filters['status'] ?? '') === 'upcoming' ? 'selected' : '' }}>即将开始</option>
                        <option value="ongoing" {{ ($filters['status'] ?? '') === 'ongoing' ? 'selected' : '' }}>进行中</option>
                        <option value="ended" {{ ($filters['status'] ?? '') === 'ended' ? 'selected' : '' }}>已结束</option>
                    </select>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-gradient text-white px-6 py-3 rounded-xl font-semibold flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>筛选</span>
                </button>

                @if(!empty($filters['search']) || !empty($filters['status']))
                    <a href="{{ route('activities.index') }}" class="px-6 py-3 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition-colors text-center">
                        清除
                    </a>
                @endif
            </form>
        </div>
    </section>

    {{-- Activities Grid --}}
    <section class="py-12 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($activities->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($activities as $activity)
                        @include('components.activity-card', ['activity' => $activity])
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-12">
                    {{ $activities->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-20">
                    <svg class="w-20 h-20 text-slate-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-2xl font-bold text-slate-700 mb-3">未找到相关活动</h3>
                    <p class="text-slate-500 mb-6">尝试调整搜索条件或清除筛选</p>
                    <a href="{{ route('activities.index') }}" class="inline-block btn-gradient text-white px-6 py-3 rounded-xl font-semibold">
                        查看全部活动
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection

