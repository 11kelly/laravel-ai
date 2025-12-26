@extends('layouts.app')

@section('title', '我的预约')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-3xl font-bold text-gray-900">我的预约</h1>
            <p class="mt-2 text-sm text-gray-700">管理您的活动预约</p>
        </div>
    </div>

    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse($bookings as $booking)
                    <li class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $booking->event->title }}</h3>
                                <div class="mt-2 space-y-1">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">时间：</span>
                                        {{ $booking->event->start_time->format('Y年m月d日 H:i') }} - {{ $booking->event->end_time->format('H:i') }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">预约时间：</span>
                                        {{ $booking->created_at->format('Y年m月d日 H:i') }}
                                    </p>
                                    @if($booking->notes)
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">备注：</span>
                                            {{ $booking->notes }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4 flex flex-col items-end space-y-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    @if($booking->status === 'confirmed') 已确认
                                    @elseif($booking->status === 'pending') 待确认
                                    @else 已取消
                                    @endif
                                </span>
                                @if($booking->status !== 'cancelled' && $booking->event->start_time > now())
                                    <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('确定要取消此预约吗？');">
                                        @csrf
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                            取消预约
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-12 text-center">
                        <p class="text-gray-500">您还没有任何预约</p>
                        <a href="{{ route('events.index') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                            浏览活动 →
                        </a>
                    </li>
                @endforelse
            </ul>
        </div>

        @if($bookings->hasPages())
            <div class="mt-6">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

