{{--
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
--}}

@extends('layouts.app')

@section('title', '我的预约')

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
                       class="flex-1 py-4 px-6 text-center font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors">
                        个人资料
                    </a>
                    <a href="{{ route('user.bookings') }}"
                       class="flex-1 py-4 px-6 text-center font-semibold border-b-2 border-primary-500 text-primary-600">
                        我的预约
                    </a>
                </nav>
            </div>

            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Bookings List --}}
            <div class="space-y-4">
                @forelse($bookings as $booking)
                    <div class="bg-white rounded-2xl shadow-sm overflow-hidden" x-data="{ showCancelModal: false }">
                        <div class="flex flex-col md:flex-row">
                            {{-- Activity Image --}}
                            <div class="md:w-48 h-40 md:h-auto flex-shrink-0">
                                @if($booking->activity->cover_image)
                                    <img src="{{ asset('storage/' . $booking->activity->cover_image) }}"
                                         alt="{{ $booking->activity->title }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            {{-- Booking Info --}}
                            <div class="flex-1 p-6">
                                <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                                    <div class="flex-1">
                                        {{-- Status Badge --}}
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                @if($booking->status === 'confirmed') bg-green-100 text-green-700
                                                @elseif($booking->status === 'pending') bg-amber-100 text-amber-700
                                                @elseif($booking->status === 'cancelled') bg-red-100 text-red-700
                                                @else bg-slate-100 text-slate-700
                                                @endif">
                                                @switch($booking->status)
                                                    @case('confirmed')
                                                        已确认
                                                        @break
                                                    @case('pending')
                                                        待确认
                                                        @break
                                                    @case('cancelled')
                                                        已取消
                                                        @break
                                                    @default
                                                        {{ $booking->status }}
                                                @endswitch
                                            </span>
                                            <span class="text-sm text-slate-500">预约编号: {{ $booking->booking_code }}</span>
                                        </div>

                                        {{-- Activity Title --}}
                                        <h3 class="text-lg font-bold text-slate-900 mb-2">
                                            <a href="{{ route('activities.show', $booking->activity->slug) }}" class="hover:text-primary-600 transition-colors">
                                                {{ $booking->activity->title }}
                                            </a>
                                        </h3>

                                        {{-- Activity Details --}}
                                        <div class="space-y-1 text-sm text-slate-600">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span>{{ $booking->activity->start_time->format('Y年m月d日 H:i') }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                </svg>
                                                <span>{{ $booking->activity->location }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>{{ $booking->participants }} 人</span>
                                            </div>
                                        </div>

                                        @if($booking->remarks)
                                            <div class="mt-3 p-3 bg-slate-50 rounded-lg">
                                                <p class="text-sm text-slate-600"><strong>备注：</strong>{{ $booking->remarks }}</p>
                                            </div>
                                        @endif

                                        @if($booking->status === 'cancelled' && $booking->cancellation_reason)
                                            <div class="mt-3 p-3 bg-red-50 rounded-lg">
                                                <p class="text-sm text-red-600"><strong>取消原因：</strong>{{ $booking->cancellation_reason }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Actions --}}
                                    <div class="mt-4 md:mt-0 md:ml-6 flex flex-col gap-2">
                                        <a href="{{ route('activities.show', $booking->activity->slug) }}"
                                           class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors text-center">
                                            查看活动
                                        </a>
                                        @if($booking->status === 'confirmed' || $booking->status === 'pending')
                                            <button @click="showCancelModal = true"
                                                    class="px-4 py-2 border border-red-200 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                                取消预约
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Cancel Modal --}}
                        <div x-show="showCancelModal"
                             x-cloak
                             x-transition
                             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                             @click.self="showCancelModal = false">
                            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl" @click.stop>
                                <h3 class="text-xl font-bold text-slate-900 mb-4">确认取消预约</h3>
                                <p class="text-slate-600 mb-4">您确定要取消此活动的预约吗？此操作无法撤销。</p>

                                <form method="POST" action="{{ route('bookings.destroy', $booking) }}">
                                    @csrf
                                    @method('DELETE')

                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-slate-700 mb-2">取消原因（可选）</label>
                                        <textarea name="cancellation_reason"
                                                  rows="3"
                                                  placeholder="请说明取消原因..."
                                                  class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-red-500 resize-none"></textarea>
                                    </div>

                                    <div class="flex space-x-4">
                                        <button type="button"
                                                @click="showCancelModal = false"
                                                class="flex-1 px-6 py-3 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                            返回
                                        </button>
                                        <button type="submit" class="flex-1 bg-red-500 text-white py-3 rounded-xl font-semibold hover:bg-red-600 transition-colors">
                                            确认取消
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
                        <svg class="w-20 h-20 text-slate-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-xl font-bold text-slate-700 mb-3">暂无预约记录</h3>
                        <p class="text-slate-500 mb-6">快去浏览活动并预约吧！</p>
                        <a href="{{ route('activities.index') }}" class="inline-block btn-gradient text-white px-6 py-3 rounded-xl font-semibold">
                            浏览活动
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($bookings->hasPages())
                <div class="mt-8">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

