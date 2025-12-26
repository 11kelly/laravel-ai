<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('messages.Event Details') }}
            </h2>
            <a href="{{ route('frontend.events.index') }}" class="text-indigo-600 hover:text-indigo-800">
                &larr; {{ __('messages.Back to List') }}
            </a>
        </div>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <!-- Event Images -->
                        @if($event->images->count() > 0)
                            <div class="aspect-video bg-gray-200">
                                <img 
                                    src="{{ $event->images->first()->full_url }}" 
                                    alt="{{ $event->title }}"
                                    class="w-full h-full object-cover"
                                >
                            </div>
                            
                            @if($event->images->count() > 1)
                                <div class="p-4 flex gap-2 overflow-x-auto">
                                    @foreach($event->images as $image)
                                        <img 
                                            src="{{ $image->full_url }}" 
                                            alt="{{ $event->title }}"
                                            class="h-20 w-32 object-cover rounded cursor-pointer hover:opacity-75"
                                        >
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        <div class="p-6">
                            <!-- Category -->
                            @if($event->category)
                            <div class="mb-4">
                                <span class="inline-block px-3 py-1 text-sm font-semibold text-indigo-600 bg-indigo-100 rounded">
                                    {{ $event->category->name }}
                                </span>
                            </div>
                            @endif

                            <!-- Title -->
                            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                                {{ $event->title }}
                            </h1>

                            <!-- Event Info -->
                            <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <div class="text-sm text-gray-500">{{ __('messages.Start Time') }}</div>
                                        <div class="font-semibold">{{ $event->start_time->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <div class="text-sm text-gray-500">{{ __('messages.End Time') }}</div>
                                        <div class="font-semibold">{{ $event->end_time->format('Y-m-d H:i') }}</div>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <div>
                                        <div class="text-sm text-gray-500">{{ __('messages.Location') }}</div>
                                        <div class="font-semibold">{{ $event->location }}</div>
                                    </div>
                                </div>

                                @if($event->max_participants)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <div>
                                            <div class="text-sm text-gray-500">{{ __('messages.Participant Limit') }}</div>
                                            <div class="font-semibold">{{ $event->max_participants }} {{ __('messages.people') }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <h2 class="text-xl font-semibold mb-3">{{ __('messages.Event Description') }}</h2>
                                <div class="prose max-w-none text-gray-700">
                                    {!! nl2br(e($event->description)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-4">{{ __('messages.Booking Information') }}</h3>

                            @auth
                                @if($has_booking)
                                    <!-- Already Booked -->
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="font-semibold text-blue-900">
                                                {{ $user_booking->status->label() }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-blue-800">
                                            {{ __('messages.Booking Count') }}：{{ $user_booking->participants_count }} {{ __('messages.people') }}
                                        </p>
                                        @if($user_booking->notes)
                                            <p class="text-sm text-blue-800 mt-2">
                                                {{ __('messages.Notes') }}：{{ $user_booking->notes }}
                                            </p>
                                        @endif
                                    </div>

                                    @if($user_booking->canBeCancelled())
                                        <form method="POST" action="{{ route('frontend.bookings.cancel', $user_booking) }}" onsubmit="return confirm('{{ __('messages.Are you sure you want to cancel this booking?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                                {{ __('messages.Cancel Booking') }}
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <!-- Booking Form -->
                                    @if($event->start_time->isPast())
                                        <div class="bg-gray-100 text-gray-600 rounded-lg p-4 text-center">
                                            {{ __('messages.Event has started, booking is not available') }}
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('frontend.bookings.store', $event) }}">
                                            @csrf
                                            
                                            <div class="mb-4">
                                                <label for="participants_count" class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ __('messages.Number of Participants') }} *
                                                </label>
                                                <input 
                                                    type="number" 
                                                    name="participants_count" 
                                                    id="participants_count"
                                                    min="1"
                                                    @if($event->max_participants) max="{{ $event->max_participants }}" @endif
                                                    value="{{ old('participants_count', 1) }}"
                                                    required
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >
                                                @error('participants_count')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="mb-4">
                                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ __('messages.Remarks') }}
                                                </label>
                                                <textarea 
                                                    name="notes" 
                                                    id="notes"
                                                    rows="3"
                                                    maxlength="500"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="{{ __('messages.If you have special requirements, please specify here...') }}"
                                                >{{ old('notes') }}</textarea>
                                                @error('notes')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <button type="submit" class="w-full px-4 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-semibold">
                                                {{ __('messages.Book Now') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            @else
                                <!-- Guest User -->
                                <div class="bg-gray-100 rounded-lg p-4 text-center">
                                    <p class="text-gray-600 mb-4">{{ __('messages.Please login to book events') }}</p>
                                    <a href="{{ route('login') }}" class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                        {{ __('messages.Login') }}
                                    </a>
                                    <a href="{{ route('register') }}" class="block w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 mt-2">
                                        {{ __('messages.Register') }}
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

