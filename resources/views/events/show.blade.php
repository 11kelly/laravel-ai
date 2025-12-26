@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', $event->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Event Header -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
        @if($event->image)
            <img src="{{ Storage::disk('public')->url($event->image) }}" alt="{{ $event->title }}" class="w-full h-64 object-cover">
        @else
            <div class="w-full h-64 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif

        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $event->title }}</h1>

            <!-- Event Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <div class="text-sm text-gray-500">开始时间</div>
                        <div class="font-medium text-gray-900">{{ $event->start_time->format('Y-m-d H:i') }}</div>
                    </div>
                </div>

                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <div class="text-sm text-gray-500">结束时间</div>
                        <div class="font-medium text-gray-900">{{ $event->end_time->format('Y-m-d H:i') }}</div>
                    </div>
                </div>

                @if($event->location)
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div>
                            <div class="text-sm text-gray-500">活动地点</div>
                            <div class="font-medium text-gray-900">{{ $event->location }}</div>
                        </div>
                    </div>
                @endif

                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <div>
                        <div class="text-sm text-gray-500">名额情况</div>
                        <div class="font-medium text-gray-900">
                            剩余 {{ $event->max_participants - $event->current_participants }} / {{ $event->max_participants }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($event->description)
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">活动描述</h2>
                    <div class="text-gray-600 whitespace-pre-line">{{ $event->description }}</div>
                </div>
            @endif

            <!-- Booking Section -->
            <div class="border-t border-gray-200 pt-6">
                @auth
                    @if($userBooking)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-blue-900">您已预约此活动</div>
                                    <div class="text-sm text-blue-700 mt-1">
                                        状态: 
                                        @if($userBooking->status === 'pending')
                                            <span class="text-yellow-600">待确认</span>
                                        @elseif($userBooking->status === 'confirmed')
                                            <span class="text-green-600">已确认</span>
                                        @else
                                            <span class="text-gray-600">已取消</span>
                                        @endif
                                    </div>
                                </div>
                                @if($userBooking->canCancel())
                                    <form method="POST" action="{{ route('bookings.destroy', $userBooking->id) }}" 
                                          onsubmit="return confirm('确定要取消预约吗？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                            取消预约
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @elseif($event->canBook())
                        <form method="POST" action="{{ route('bookings.store') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                            
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">备注（可选）</label>
                                <textarea name="notes" id="notes" rows="3" 
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="如有特殊需求，请在此说明..."></textarea>
                            </div>

                            <button type="submit" 
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition">
                                立即预约
                            </button>
                        </form>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                            <p class="text-gray-600">
                                @if($event->current_participants >= $event->max_participants)
                                    活动名额已满
                                @elseif($event->booking_deadline && now()->isAfter($event->booking_deadline))
                                    预约已截止
                                @elseif(now()->isAfter($event->start_time))
                                    活动已开始
                                @else
                                    暂不可预约
                                @endif
                            </p>
                        </div>
                    @endif
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                        <p class="text-gray-600 mb-4">请先登录后再预约活动</p>
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" 
                           class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg transition">
                            立即登录
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('events.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            返回活动列表
        </a>
    </div>
</div>
@endsection

