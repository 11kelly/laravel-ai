@extends('layouts.app')

@section('title', '活动列表')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">活动列表</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">浏览并预约您感兴趣的活动</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($activities as $activity)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                @if($activity->image_url)
                    <img src="{{ $activity->image_url }}" alt="{{ $activity->title }}" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        <span class="text-gray-400 dark:text-gray-500">暂无图片</span>
                    </div>
                @endif
                
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        <a href="{{ route('activities.show', $activity->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                            {{ $activity->title }}
                        </a>
                    </h2>
                    
                    @if($activity->short_description)
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">
                            {{ $activity->short_description }}
                        </p>
                    @endif

                    <div class="space-y-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $activity->start_time->format('Y-m-d H:i') }}
                        </div>
                        
                        @if($activity->location)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $activity->location }}
                            </div>
                        @endif

                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            @if($activity->max_participants > 0)
                                已报名: {{ $activity->current_participants }} / {{ $activity->max_participants }}
                            @else
                                已报名: {{ $activity->current_participants }} (无限制)
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('activities.show', $activity->id) }}" class="inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                        查看详情
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">暂无活动</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $activities->links() }}
    </div>
</div>
@endsection

