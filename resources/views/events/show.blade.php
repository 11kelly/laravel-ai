@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        @if($event->image)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($event->image) }}" alt="{{ $event->title }}" class="w-full h-96 object-cover">
        @else
            <div class="w-full h-96 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                <span class="text-gray-400 text-xl">暂无图片</span>
            </div>
        @endif

        <div class="p-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ $event->title }}</h1>

            <div class="space-y-4 mb-6">
                <div class="flex items-center text-gray-600 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium">开始时间:</span>
                    <span class="ml-2">{{ $event->start_time->format('Y年m月d日 H:i') }}</span>
                </div>
                <div class="flex items-center text-gray-600 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium">结束时间:</span>
                    <span class="ml-2">{{ $event->end_time->format('Y年m月d日 H:i') }}</span>
                </div>
                <div class="flex items-center text-gray-600 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium">地点:</span>
                    <span class="ml-2">{{ $event->location }}</span>
                </div>
                <div class="flex items-center text-gray-600 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium">容量:</span>
                    <span class="ml-2">已预约 {{ $event->booked_count }} / {{ $event->capacity }}</span>
                    @if($event->hasAvailableSlots())
                        <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">还有 {{ $event->getAvailableSlots() }} 个名额</span>
                    @else
                        <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">已满</span>
                    @endif
                </div>
            </div>

            @if($event->description)
                <div class="prose dark:prose-invert max-w-none mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">活动描述</h2>
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $event->description }}</p>
                </div>
            @endif

            @auth
                @php
                    $userBooking = Auth::user()->bookings()->where('event_id', $event->id)->first();
                @endphp

                @if($userBooking)
                    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <p class="text-yellow-800 dark:text-yellow-200">
                            您已预约此活动，状态:
                            @if($userBooking->isPending())
                                <span class="font-semibold">待确认</span>
                            @elseif($userBooking->isConfirmed())
                                <span class="font-semibold text-green-600 dark:text-green-400">已确认</span>
                            @else
                                <span class="font-semibold text-red-600 dark:text-red-400">已取消</span>
                            @endif
                        </p>
                    </div>
                @elseif($event->hasAvailableSlots() && $event->start_time > now())
                    <form method="POST" action="{{ route('bookings.store', $event) }}" class="mt-6">
                        @csrf
                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">备注（可选）</label>
                            <textarea name="notes" id="notes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="请输入备注信息..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md text-lg font-medium">
                            立即预约
                        </button>
                    </form>
                @elseif($event->isFull())
                    <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-red-800 dark:text-red-200 font-semibold">活动名额已满</p>
                    </div>
                @elseif($event->start_time <= now())
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <p class="text-gray-800 dark:text-gray-200 font-semibold">活动已开始或已结束</p>
                    </div>
                @endif
            @else
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <p class="text-blue-800 dark:text-blue-200">
                        请 <a href="{{ route('login') }}" class="underline font-semibold">登录</a> 后预约此活动
                    </p>
                </div>
            @endauth

            <div class="mt-6">
                <a href="{{ route('events.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← 返回活动列表
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

