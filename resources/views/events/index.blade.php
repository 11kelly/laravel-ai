{{--
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
--}}
@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Upcoming Events</h1>
        <p class="text-gray-600">Discover and book amazing events</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('events.index') }}" class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search events..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>

            <!-- Category Filter -->
            <div class="md:w-64">
                <select name="category" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Search
            </button>

            @if(request('search') || request('category'))
                <a href="{{ route('events.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Events Grid -->
    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- Event Image -->
                    <div class="relative h-48 bg-gray-200">
                        @if($event->cover_image_url)
                            <img src="{{ $event->cover_image_url }}" 
                                 alt="{{ $event->title }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600">
                                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <!-- Category Badge -->
                        @if($event->category)
                            <div class="absolute top-2 left-2">
                                <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-indigo-600 text-xs font-semibold rounded-full">
                                    {{ $event->category?->name ?? '未分类' }}
                                </span>
                            </div>
                        @endif

                        <!-- Availability Badge -->
                        <div class="absolute top-2 right-2">
                            @if($event->booked_count >= $event->capacity)
                                <span class="px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-full">
                                    Full
                                </span>
                            @elseif($event->getRemainingCapacity() <= 10)
                                <span class="px-3 py-1 bg-yellow-500 text-white text-xs font-semibold rounded-full">
                                    {{ $event->getRemainingCapacity() }} left
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Event Details -->
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                            {{ $event->title }}
                        </h3>

                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <!-- Date -->
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $event->start_time->format('M d, Y H:i') }}
                            </div>

                            <!-- Location -->
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="line-clamp-1">{{ $event->location }}</span>
                            </div>

                            <!-- Capacity -->
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                {{ $event->booked_count }} / {{ $event->capacity }} booked
                            </div>
                        </div>

                        <!-- Action Button -->
                        <a href="{{ route('events.show', $event->slug) }}" 
                           class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            View Details
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
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No events found</h3>
            <p class="text-gray-600">Try adjusting your search or filters</p>
        </div>
    @endif
</div>
@endsection

