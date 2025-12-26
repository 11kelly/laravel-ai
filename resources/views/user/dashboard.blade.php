@extends('layouts.app')

@section('title', '个人中心')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">个人中心</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 个人信息 -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">个人信息</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">姓名</p>
                        <p class="text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">邮箱</p>
                        <p class="text-gray-900 dark:text-white">{{ Auth::user()->email }}</p>
                    </div>
                    @if(Auth::user()->phone)
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">手机</p>
                            <p class="text-gray-900 dark:text-white">{{ Auth::user()->phone }}</p>
                        </div>
                    @endif
                </div>
                <a href="{{ route('user.profile.show') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                    编辑资料 →
                </a>
            </div>
        </div>

        <!-- 预约列表 -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">我的预约</h2>

                @forelse($bookings as $booking)
                    <div class="border-b border-gray-200 dark:border-gray-700 py-4 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                    <a href="{{ route('activities.show', $booking->activity_id) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $booking->activity->title }}
                                    </a>
                                </h3>
                                <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <p>时间: {{ $booking->activity->start_time->format('Y-m-d H:i') }}</p>
                                    @if($booking->activity->location)
                                        <p>地点: {{ $booking->activity->location }}</p>
                                    @endif
                                    <p>
                                        状态: 
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            @if($booking->status === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @endif">
                                            @if($booking->status === 'confirmed') 已确认
                                            @elseif($booking->status === 'pending') 待确认
                                            @else 已取消
                                            @endif
                                        </span>
                                    </p>
                                    <p>预约时间: {{ $booking->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                            <div class="ml-4">
                                @if($booking->canBeCancelled())
                                    <button onclick="cancelBooking({{ $booking->id }})" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm">
                                        取消预约
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">暂无预约记录</p>
                @endforelse

                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelBooking(bookingId) {
    if (!confirm('确定要取消这个预约吗？')) {
        return;
    }

    fetch(`/user/bookings/${bookingId}/cancel`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
        } else {
            alert('取消成功！');
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('取消失败，请稍后重试');
    });
}
</script>
@endsection

