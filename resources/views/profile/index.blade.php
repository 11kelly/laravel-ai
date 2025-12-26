{{--
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
--}}
@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('info') }}
        </div>
    @endif

    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Profile</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6" x-data="profileForm()">
                <div class="text-center mb-6">
                    <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                         alt="Avatar" 
                         class="w-24 h-24 rounded-full mx-auto mb-4">
                    <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                </div>

                <hr class="my-6">

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                   required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input type="text" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Avatar</label>
                            <input type="file" 
                                   name="avatar" 
                                   accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('avatar') border-red-500 @enderror">
                            @error('avatar')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" 
                                class="w-full px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bookings -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">My Bookings</h2>

                <!-- Status Tabs -->
                <div class="flex space-x-4 mb-6 border-b">
                    <a href="{{ route('profile.index', ['status' => 'confirmed']) }}" 
                       class="pb-3 px-4 {{ $status === 'confirmed' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                        Active
                    </a>
                    <a href="{{ route('profile.index', ['status' => 'completed']) }}" 
                       class="pb-3 px-4 {{ $status === 'completed' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                        Completed
                    </a>
                    <a href="{{ route('profile.index', ['status' => 'cancelled']) }}" 
                       class="pb-3 px-4 {{ $status === 'cancelled' ? 'border-b-2 border-indigo-600 text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                        Cancelled
                    </a>
                </div>

                <!-- Bookings List -->
                @if($bookings->count() > 0)
                    <div class="space-y-4">
                        @foreach($bookings as $booking)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition" x-data="{ showCancel: false }">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 mr-3">
                                                {{ $booking->event->title }}
                                            </h3>
                                            @if($booking->status === 'confirmed')
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                                    Confirmed
                                                </span>
                                            @elseif($booking->status === 'cancelled')
                                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                                    Cancelled
                                                </span>
                                            @else
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                                    Completed
                                                </span>
                                            @endif
                                        </div>

                                        <div class="space-y-1 text-sm text-gray-600">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $booking->event->start_time->format('M d, Y H:i') }}
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                {{ $booking->event->location }}
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                {{ $booking->participants_count }} participant(s)
                                            </div>
                                            <div class="text-xs text-gray-500 mt-2">
                                                Booked: {{ $booking->created_at->format('M d, Y H:i') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end space-y-2">
                                        <a href="{{ route('events.show', $booking->event->slug) }}" 
                                           class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                                            View Event
                                        </a>

                                        @if($booking->canBeCancelled())
                                            <button @click="showCancel = true" 
                                                    class="text-red-600 hover:text-red-700 text-sm font-medium">
                                                Cancel Booking
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Cancel Confirmation -->
                                <div x-show="showCancel" 
                                     x-transition
                                     class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm text-red-800 mb-3">
                                        Are you sure you want to cancel this booking? This action cannot be undone.
                                    </p>
                                    <div class="flex space-x-3">
                                        <form method="POST" action="{{ route('bookings.destroy', $booking->id) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
                                                Yes, Cancel
                                            </button>
                                        </form>
                                        <button @click="showCancel = false" 
                                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                                            No, Keep It
                                        </button>
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
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No bookings found</h3>
                        <p class="text-gray-600 mb-4">You haven't booked any events yet</p>
                        <a href="{{ route('events.index') }}" 
                           class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Browse Events
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function profileForm() {
    return {
        // Add any interactive functionality here
    };
}
</script>
@endsection

