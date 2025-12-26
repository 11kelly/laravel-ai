@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('events.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← 返回活动列表
        </a>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            @if($event->image_path)
                <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-96 object-cover">
            @else
                <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                    <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            @endif

            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $event->title }}</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="text-sm font-medium">开始时间</div>
                            <div>{{ $event->start_time->format('Y年m月d日 H:i') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <div class="text-sm font-medium">结束时间</div>
                            <div>{{ $event->end_time->format('Y年m月d日 H:i') }}</div>
                        </div>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <div>
                            <div class="text-sm font-medium">参与人数</div>
                            <div>{{ $event->remaining_capacity }} / {{ $event->capacity }} 剩余</div>
                        </div>
                    </div>
                </div>

                <div class="prose max-w-none mb-6">
                    <h2 class="text-xl font-semibold mb-2">活动描述</h2>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $event->description }}</p>
                </div>

                @auth
                    @php
                        $userBooking = \App\Models\Booking::where('event_id', $event->id)
                            ->where('user_id', Auth::id())
                            ->first();
                    @endphp

                    @if($userBooking)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                            <p class="text-yellow-800">您已预约此活动</p>
                        </div>
                    @elseif($event->remaining_capacity > 0 && $event->start_time > now())
                        <form action="{{ route('bookings.store') }}" method="POST" class="mt-6">
                            @csrf
                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">备注（可选）</label>
                                <textarea name="notes" id="notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" maxlength="500"></textarea>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors font-medium">
                                立即预约
                            </button>
                        </form>
                    @elseif($event->remaining_capacity <= 0)
                        <div class="bg-red-50 border border-red-200 rounded-md p-4 mt-6">
                            <p class="text-red-800">活动已满</p>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4 mt-6">
                            <p class="text-gray-800">活动已开始</p>
                        </div>
                    @endif
                @else
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mt-6">
                        <p class="text-blue-800 mb-2">请先登录以预约活动</p>
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 underline">立即登录</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection

