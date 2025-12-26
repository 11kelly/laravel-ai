<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('我的预约') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            @if($bookings->count() > 0)
                <div class="space-y-4">
                    @foreach($bookings as $booking)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row gap-6">
                                    <!-- Event Image -->
                                    <div class="md:w-48 flex-shrink-0">
                                        <div class="aspect-video bg-gray-200 rounded-lg overflow-hidden">
                                            @if($booking->event->images->count() > 0)
                                                <img 
                                                    src="{{ $booking->event->images->first()->full_url }}" 
                                                    alt="{{ $booking->event->title }}"
                                                    class="w-full h-full object-cover"
                                                >
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Booking Details -->
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                @if($booking->event->category)
                                                    <span class="inline-block px-2 py-1 text-xs font-semibold text-indigo-600 bg-indigo-100 rounded mb-2">
                                                        {{ $booking->event->category->name }}
                                                    </span>
                                                @endif
                                                <h3 class="text-xl font-semibold text-gray-900">
                                                    {{ $booking->event->title }}
                                                </h3>
                                            </div>
                                            
                                            <!-- Status Badge -->
                                            <span class="inline-block px-3 py-1 text-sm font-semibold rounded
                                                @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($booking->status === 'confirmed') bg-green-100 text-green-800
                                                @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                                @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                                                @endif
                                            ">
                                                @if($booking->status === 'pending') 待审批
                                                @elseif($booking->status === 'confirmed') 已确认
                                                @elseif($booking->status === 'cancelled') 已取消
                                                @elseif($booking->status === 'completed') 已完成
                                                @endif
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-600 mb-4">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                活动时间：{{ $booking->event->start_time->format('Y-m-d H:i') }}
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                地点：{{ $booking->event->location }}
                                            </div>

                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                预约人数：{{ $booking->participants_count }} 人
                                            </div>

                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                预约时间：{{ $booking->created_at->format('Y-m-d H:i') }}
                                            </div>
                                        </div>

                                        @if($booking->notes)
                                            <div class="text-sm text-gray-600 mb-4">
                                                <strong>备注：</strong>{{ $booking->notes }}
                                            </div>
                                        @endif

                                        @if($booking->status === 'cancelled')
                                            <div class="text-sm text-red-600 bg-red-50 p-3 rounded">
                                                <strong>取消原因：</strong>
                                                {{ $booking->cancellation_reason ?? '无' }}
                                                <div class="mt-1 text-xs">
                                                    取消时间：{{ $booking->cancelled_at->format('Y-m-d H:i') }}
                                                    （{{ $booking->cancelled_by === 'user' ? '用户取消' : '管理员取消' }}）
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Actions -->
                                        <div class="flex gap-2 mt-4">
                                            <a 
                                                href="{{ route('frontend.events.show', $booking->event) }}" 
                                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                                            >
                                                查看活动详情
                                            </a>

                                            @if($booking->canBeCancelled())
                                                <form 
                                                    method="POST" 
                                                    action="{{ route('frontend.bookings.cancel', $booking) }}"
                                                    onsubmit="return confirm('确定要取消预约吗？')"
                                                    class="inline"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                                                        取消预约
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-lg mb-4">暂无预约记录</p>
                        <a href="{{ route('frontend.events.index') }}" class="inline-block px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            浏览活动
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

