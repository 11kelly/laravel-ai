{{--
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
--}}
@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Button -->
    <a href="{{ route('events.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 mb-6">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Events
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Event Image -->
            <div class="relative h-96 bg-gray-200 rounded-lg overflow-hidden mb-6">
                @if($event->cover_image_url)
                    <img src="{{ $event->cover_image_url }}" 
                         alt="{{ $event->title }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600">
                        <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif

                @if($event->category)
                    <div class="absolute top-4 left-4">
                        <span class="px-4 py-2 bg-white/90 backdrop-blur-sm text-indigo-600 text-sm font-semibold rounded-full">
                            {{ $event->category?->name ?? '未分类' }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Event Title & Description -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $event->title }}</h1>
                
                <div class="prose max-w-none text-gray-700">
                    {!! nl2br(e($event->description)) !!}
                </div>
            </div>

            <!-- Additional Images -->
            @if($event->images->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Gallery</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($event->images as $image)
                            <img src="{{ $image->full_url }}" 
                                 alt="Event image" 
                                 class="w-full h-48 object-cover rounded-lg">
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Booking Rules -->
            @if($event->booking_rules)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Booking Rules</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($event->booking_rules)) !!}
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Booking Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-20">
                {{-- Flash Messages --}}
                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Book This Event</h2>

                <!-- Event Info -->
                <div class="space-y-3 mb-6 text-sm">
                    <!-- Date -->
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $event->start_time->format('l, F j, Y') }}</p>
                            <p class="text-gray-600">{{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }}</p>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-gray-900">{{ $event->location }}</p>
                    </div>

                    <!-- Capacity -->
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-3 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $event->getRemainingCapacity() }} spots remaining</p>
                            <p class="text-gray-600">{{ $event->booked_count }} / {{ $event->capacity }} booked</p>
                        </div>
                    </div>

                    <!-- Organizer -->
                    @if($event->organizer_name)
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mr-3 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $event->organizer_name }}</p>
                                @if($event->organizer_contact)
                                    <p class="text-gray-600">{{ $event->organizer_contact }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="my-6">

                <!-- Booking Status -->
                @if($userBooking)
                    <!-- Already Booked -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="font-semibold text-green-800">You're booked!</span>
                        </div>
                        <p class="text-sm text-green-700">
                            Participants: {{ $userBooking->participants_count }}
                        </p>
                    </div>
                    
                    <a href="{{ route('profile.index') }}" 
                       class="block w-full text-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        View My Bookings
                    </a>
                @elseif(!$event->isBookable())
                    <!-- Not Bookable -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-red-700 font-semibold">
                            @if($event->booked_count >= $event->capacity)
                                This event is fully booked
                            @elseif($event->start_time->isPast())
                                This event has already started
                            @elseif($event->booking_deadline && $event->booking_deadline->isPast())
                                Booking deadline has passed
                            @else
                                This event is not available for booking
                            @endif
                        </p>
                    </div>
                @elseif(!auth()->check())
                    <!-- Not Logged In -->
                    <p class="text-sm text-gray-600 mb-4">Please login to book this event</p>
                    <a href="{{ route('login') }}" 
                       class="block w-full text-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        Login to Book
                    </a>
                @else
                    <!-- Booking Form -->
                    <form method="POST" action="{{ route('bookings.store') }}">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Number of Participants
                            </label>
                            <input type="number" 
                                   name="participants_count"
                                   value="1"
                                   min="1" 
                                   max="{{ min(10, $event->getRemainingCapacity()) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('participants_count') border-red-500 @enderror"
                                   required>
                            @error('participants_count')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea name="notes"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                                      placeholder="Any special requirements..."></textarea>
                            @error('notes')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" 
                                class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold">
                            Book Now
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

