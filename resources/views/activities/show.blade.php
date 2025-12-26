{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

@extends('layouts.app')

@section('title', $activity->title)

@section('content')
    {{-- Hero Image --}}
    <section class="relative h-[400px] md:h-[500px] bg-slate-900">
        @if($activity->cover_image)
            <img src="{{ asset('storage/' . $activity->cover_image) }}"
                 alt="{{ $activity->title }}"
                 class="w-full h-full object-cover opacity-60">
        @else
            <div class="w-full h-full bg-gradient-to-br from-primary-600 to-accent-600"></div>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent"></div>

        <div class="absolute bottom-0 left-0 right-0 p-8 md:p-12">
            <div class="max-w-7xl mx-auto">
                {{-- Badges --}}
                <div class="flex flex-wrap gap-2 mb-4">
                    @if($activity->is_featured)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-amber-400 to-orange-500 text-white">
                            ⭐ 推荐活动
                        </span>
                    @endif
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($activity->status_label === '可预约') bg-green-500
                        @elseif($activity->status_label === '已满') bg-red-500
                        @elseif($activity->status_label === '进行中') bg-blue-500
                        @else bg-slate-500
                        @endif text-white">
                        {{ $activity->status_label }}
                    </span>
                </div>

                <h1 class="text-3xl md:text-5xl font-bold text-white mb-4">{{ $activity->title }}</h1>

                <div class="flex flex-wrap items-center gap-6 text-white/80">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $activity->start_time->format('Y年m月d日 H:i') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span>{{ $activity->location }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Content --}}
    <section class="py-12 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Description --}}
                    @if($activity->description)
                        <div class="bg-white rounded-2xl p-6 shadow-sm">
                            <h2 class="text-xl font-bold text-slate-900 mb-4">活动简介</h2>
                            <p class="text-slate-600 leading-relaxed">{{ $activity->description }}</p>
                        </div>
                    @endif

                    {{-- Detail Content --}}
                    @if($activity->content)
                        <div class="bg-white rounded-2xl p-6 shadow-sm">
                            <h2 class="text-xl font-bold text-slate-900 mb-4">详细内容</h2>
                            <div class="prose prose-slate max-w-none">
                                {{-- 使用 safe_content 属性，确保 HTML 已被净化防止 XSS --}}
                                {!! $activity->safe_content !!}
                            </div>
                        </div>
                    @endif

                    {{-- Gallery --}}
                    @if($activity->gallery && count($activity->gallery) > 0)
                        <div class="bg-white rounded-2xl p-6 shadow-sm">
                            <h2 class="text-xl font-bold text-slate-900 mb-4">活动图集</h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($activity->gallery as $image)
                                    <div class="aspect-square rounded-xl overflow-hidden">
                                        <img src="{{ asset('storage/' . $image) }}"
                                             alt="活动图片"
                                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300 cursor-pointer">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Booking Card --}}
                    <div class="bg-white rounded-2xl p-6 shadow-lg sticky top-24">
                        {{-- Capacity Progress --}}
                        <div class="mb-6">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-slate-600">已预约人数</span>
                                <span class="font-semibold text-slate-900">{{ $activity->booked_count }} / {{ $activity->capacity }}</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-primary-500 to-accent-500 h-3 rounded-full transition-all duration-500"
                                     style="width: {{ min(100, ($activity->booked_count / $activity->capacity) * 100) }}%"></div>
                            </div>
                            <p class="text-sm text-slate-500 mt-2">
                                剩余 <span class="font-semibold text-primary-600">{{ $activity->remaining_capacity }}</span> 个名额
                            </p>
                        </div>

                        {{-- Info List --}}
                        <div class="space-y-4 mb-6">
                            <div class="flex items-start">
                                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-slate-500">活动时间</p>
                                    <p class="font-semibold text-slate-900">{{ $activity->start_time->format('Y年m月d日') }}</p>
                                    <p class="text-sm text-slate-600">{{ $activity->start_time->format('H:i') }} - {{ $activity->end_time->format('H:i') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="w-10 h-10 rounded-xl bg-accent-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-slate-500">活动地点</p>
                                    <p class="font-semibold text-slate-900">{{ $activity->location }}</p>
                                    @if($activity->address)
                                        <p class="text-sm text-slate-600">{{ $activity->address }}</p>
                                    @endif
                                </div>
                            </div>

                            @if($activity->registration_deadline)
                                <div class="flex items-start">
                                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm text-slate-500">报名截止</p>
                                        <p class="font-semibold text-slate-900">{{ $activity->registration_deadline->format('Y年m月d日 H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Booking Button --}}
                        @auth
                            @if($hasBooked)
                                <div class="text-center">
                                    <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl mb-4">
                                        <p class="font-semibold">✓ 您已成功预约此活动</p>
                                        @if($userBooking)
                                            <p class="text-sm mt-1">预约编号：{{ $userBooking->booking_code }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('user.bookings') }}" class="text-primary-600 hover:underline text-sm font-medium">
                                        查看我的预约 →
                                    </a>
                                </div>
                            @elseif($activity->is_available)
                                <div x-data="{ showModal: false }">
                                    <button @click="showModal = true" class="w-full btn-gradient text-white py-4 rounded-xl font-bold text-lg">
                                        立即预约
                                    </button>

                                    {{-- Booking Modal --}}
                                    <div x-show="showModal"
                                         x-cloak
                                         x-transition
                                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                                         @click.self="showModal = false">
                                        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl" @click.stop>
                                            <h3 class="text-xl font-bold text-slate-900 mb-4">确认预约</h3>
                                            <form method="POST" action="{{ route('bookings.store') }}">
                                                @csrf
                                                <input type="hidden" name="activity_id" value="{{ $activity->id }}">

                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-slate-700 mb-2">参与人数</label>
                                                    <select name="participants" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500">
                                                        @for($i = 1; $i <= min(10, $activity->remaining_capacity); $i++)
                                                            <option value="{{ $i }}">{{ $i }} 人</option>
                                                        @endfor
                                                    </select>
                                                </div>

                                                <div class="mb-6">
                                                    <label class="block text-sm font-medium text-slate-700 mb-2">备注（可选）</label>
                                                    <textarea name="remarks"
                                                              rows="3"
                                                              placeholder="如有特殊需求，请在此说明..."
                                                              class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                                                </div>

                                                <div class="flex space-x-4">
                                                    <button type="button"
                                                            @click="showModal = false"
                                                            class="flex-1 px-6 py-3 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                                        取消
                                                    </button>
                                                    <button type="submit" class="flex-1 btn-gradient text-white py-3 rounded-xl font-semibold">
                                                        确认预约
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <button disabled class="w-full bg-slate-200 text-slate-500 py-4 rounded-xl font-bold text-lg cursor-not-allowed">
                                    {{ $activity->status_label }}
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="block w-full btn-gradient text-white py-4 rounded-xl font-bold text-lg text-center">
                                登录后预约
                            </a>
                            <p class="text-center text-sm text-slate-500 mt-3">
                                还没有账号？<a href="{{ route('register') }}" class="text-primary-600 hover:underline">立即注册</a>
                            </p>
                        @endauth
                    </div>

                    {{-- Organizer --}}
                    @if($activity->creator)
                        <div class="bg-white rounded-2xl p-6 shadow-sm">
                            <h3 class="text-lg font-bold text-slate-900 mb-4">主办方</h3>
                            <div class="flex items-center">
                                <img src="{{ $activity->creator->avatar_url }}"
                                     alt="{{ $activity->creator->name }}"
                                     class="w-12 h-12 rounded-full object-cover">
                                <div class="ml-4">
                                    <p class="font-semibold text-slate-900">{{ $activity->creator->name }}</p>
                                    <p class="text-sm text-slate-500">活动发布者</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

