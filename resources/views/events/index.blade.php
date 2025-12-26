@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', '活动列表')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">活动列表</h1>
        <p class="mt-2 text-gray-600">浏览并预约感兴趣的活动</p>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex space-x-4">
        <a href="{{ route('events.index', ['status' => 'upcoming']) }}" 
           class="px-4 py-2 rounded-lg {{ $status === 'upcoming' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-300 transition">
            即将开始
        </a>
        <a href="{{ route('events.index', ['status' => 'ongoing']) }}" 
           class="px-4 py-2 rounded-lg {{ $status === 'ongoing' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-300 transition">
            进行中
        </a>
        <a href="{{ route('events.index', ['status' => 'ended']) }}" 
           class="px-4 py-2 rounded-lg {{ $status === 'ended' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} border border-gray-300 transition">
            已结束
        </a>
    </div>

    <!-- Events Grid -->
    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden border border-gray-200">
                    @if($event->image)
                        <img src="{{ Storage::disk('public')->url($event->image) }}" alt="{{ $event->title }}" class="w-full h-48 object-cover" loading="lazy">
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif

                    <div class="p-5">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $event->title }}</h3>
                        
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $event->start_time->format('Y-m-d H:i') }}
                            </div>
                            
                            @if($event->location)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $event->location }}
                                </div>
                            @endif

                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                剩余名额: {{ $event->max_participants - $event->current_participants }} / {{ $event->max_participants }}
                            </div>
                        </div>

                        <a href="{{ route('events.show', $event->id) }}" 
                           class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition">
                            查看详情
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $events->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">暂无活动</h3>
            <p class="mt-1 text-sm text-gray-500">当前没有符合条件的活动</p>
        </div>
    @endif
</div>
@endsection

