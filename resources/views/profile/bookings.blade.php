@extends('layouts.app')

@section('title', '我的预约')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">我的预约</h1>
        <p class="mt-2 text-gray-600">查看和管理您的活动预约</p>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex space-x-4">
        <a href="{{ route('profile.bookings') }}" 
           class="px-4 py-2 rounded-lg {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-300 transition">
            全部
        </a>
        <a href="{{ route('profile.bookings', ['status' => 'pending']) }}" 
           class="px-4 py-2 rounded-lg {{ request('status') === 'pending' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-300 transition">
            待确认
        </a>
        <a href="{{ route('profile.bookings', ['status' => 'confirmed']) }}" 
           class="px-4 py-2 rounded-lg {{ request('status') === 'confirmed' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-300 transition">
            已确认
        </a>
        <a href="{{ route('profile.bookings', ['status' => 'cancelled']) }}" 
           class="px-4 py-2 rounded-lg {{ request('status') === 'cancelled' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-300 transition">
            已取消
        </a>
    </div>

    <!-- Bookings List -->
    @if($bookings->count() > 0)
        <div class="space-y-4">
            @foreach($bookings as $booking)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $booking->event->title }}</h3>
                                <span class="ml-3 px-3 py-1 text-xs font-medium rounded-full
                                    @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($booking->status === 'confirmed') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($booking->status === 'pending') 待确认
                                    @elseif($booking->status === 'confirmed') 已确认
                                    @else 已取消
                                    @endif
                                </span>
                            </div>

                            <div class="space-y-1 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $booking->event->start_time->format('Y-m-d H:i') }}
                                </div>
                                @if($booking->event->location)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $booking->event->location }}
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500 mt-2">
                                    预约时间: {{ $booking->created_at->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            @if($booking->notes)
                                <div class="mt-3 text-sm text-gray-600">
                                    <span class="font-medium">备注:</span> {{ $booking->notes }}
                                </div>
                            @endif
                        </div>

                        <div class="ml-4 flex flex-col space-y-2">
                            <a href="{{ route('events.show', $booking->event_id) }}" 
                               class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                                查看活动
                            </a>
                            @if($booking->canCancel())
                                <form method="POST" action="{{ route('bookings.destroy', $booking->id) }}" 
                                      onsubmit="return confirm('确定要取消预约吗？');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                        取消预约
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    @else
        <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">暂无预约</h3>
            <p class="mt-1 text-sm text-gray-500">您还没有预约任何活动</p>
            <div class="mt-6">
                <a href="{{ route('events.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    浏览活动
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

