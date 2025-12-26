@extends('layouts.app')

@section('title', $activity->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        @if($activity->image_url)
            <img src="{{ $activity->image_url }}" alt="{{ $activity->title }}" class="w-full h-64 md:h-96 object-cover">
        @endif

        <div class="p-6 md:p-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ $activity->title }}</h1>

            <div class="space-y-4 mb-6">
                <div class="flex items-center text-gray-600 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>开始时间: {{ $activity->start_time->format('Y-m-d H:i') }}</span>
                </div>

                <div class="flex items-center text-gray-600 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>结束时间: {{ $activity->end_time->format('Y-m-d H:i') }}</span>
                </div>

                @if($activity->location)
                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ $activity->location }}</span>
                    </div>
                @endif

                <div class="flex items-center text-gray-600 dark:text-gray-400">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>
                        @if($activity->max_participants > 0)
                            已报名: {{ $activity->current_participants }} / {{ $activity->max_participants }}
                            @if($activity->isAvailableForBooking())
                                (剩余 {{ $activity->max_participants - $activity->current_participants }} 个名额)
                            @endif
                        @else
                            已报名: {{ $activity->current_participants }} (无限制)
                        @endif
                    </span>
                </div>

                <div class="flex items-center">
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        @if($activity->status === 'published') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($activity->status === 'draft') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @endif">
                        @if($activity->status === 'published')
                            @if($activity->temporal_status === 'upcoming') 即将开始
                            @elseif($activity->temporal_status === 'ongoing') 进行中
                            @elseif($activity->temporal_status === 'ended') 已结束
                            @endif
                        @elseif($activity->status === 'draft')
                            草稿
                        @else
                            已取消
                        @endif
                    </span>
                </div>
            </div>

            @if($activity->description)
                <div class="prose dark:prose-invert max-w-none mb-6">
                    {!! $activity->description !!}
                </div>
            @endif

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                @auth
                    @if($activity->isAvailableForBooking())
                        <button id="bookBtn" onclick="bookActivity({{ $activity->id }})" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors">
                            立即预约
                        </button>
                    @else
                        <button disabled class="w-full bg-gray-400 text-white font-medium py-3 px-6 rounded-md cursor-not-allowed">
                            @if($activity->status !== 'published')
                                活动未发布
                            @elseif($activity->start_time <= now())
                                活动已开始或已结束
                            @elseif($activity->max_participants > 0 && $activity->current_participants >= $activity->max_participants)
                                活动已满
                            @else
                                无法预约
                            @endif
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors">
                        登录后预约
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>

<script>
function bookActivity(activityId) {
    const btn = document.getElementById('bookBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '预约中...';

    fetch(`/activities/${activityId}/bookings`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => {
        const isOk = response.ok;
        return response.json().then(data => ({ data, isOk }));
    })
    .then(({ data, isOk }) => {
        if (isOk) {
            alert('预约成功！');
            window.location.href = '{{ route("user.dashboard") }}';
        } else {
            alert(data.message || '预约失败，请稍后重试');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('预约失败，请稍后重试');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = originalText;
    });
}
</script>
@endsection

