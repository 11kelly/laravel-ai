<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('messages.My Bookings') }}
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
                                            @php
                                                $status = $booking->status;
                                                $statusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'confirmed' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    'completed' => 'bg-blue-100 text-blue-800',
                                                ];
                                                $colorClass = $statusColors[$status->value] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-block px-3 py-1 text-sm font-semibold rounded {{ $colorClass }}">
                                                {{ $status->label() }}
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-600 mb-4">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ __('messages.Event Time') }}：{{ $booking->event->start_time->format('Y-m-d H:i') }}
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                {{ __('messages.Location') }}：{{ $booking->event->location }}
                                            </div>

                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                {{ __('messages.Number of Participants') }}：{{ $booking->participants_count }} {{ __('messages.people') }}
                                            </div>

                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ __('messages.Booking Time') }}：{{ $booking->created_at->format('Y-m-d H:i') }}
                                            </div>
                                        </div>

                                        @if($booking->notes)
                                            <div class="text-sm text-gray-600 mb-4">
                                                <strong>{{ __('messages.Notes') }}：</strong>{{ $booking->notes }}
                                            </div>
                                        @endif

                                        @if($booking->status === \App\Enums\BookingStatus::PENDING)
                                            <div class="text-sm text-yellow-700 bg-yellow-50 p-3 rounded mb-4">
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <div>
                                                        <strong>{{ __('messages.Pending Approval') }}</strong>
                                                        <p class="mt-1 text-xs text-yellow-600">
                                                            {{ __('messages.Booking Time') }}：{{ $booking->created_at->format('Y-m-d H:i') }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-yellow-600">
                                                            {{ __('messages.Your booking is pending admin approval') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($booking->status === \App\Enums\BookingStatus::CONFIRMED && $booking->approved_at)
                                            <div class="text-sm text-green-700 bg-green-50 p-3 rounded mb-4">
                                                <div class="flex items-start">
                                                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <div>
                                                        <strong>{{ __('messages.Booking Confirmed') }}</strong>
                                                        <p class="mt-1 text-xs text-green-600">
                                                            {{ __('messages.Approved At') }}：{{ $booking->approved_at->format('Y-m-d H:i') }}
                                                            @if($booking->approver)
                                                                （{{ __('messages.Approved by') }}：{{ $booking->approver->name }}）
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($booking->status === \App\Enums\BookingStatus::CANCELLED)
                                            <div class="text-sm text-red-600 bg-red-50 p-3 rounded mb-4">
                                                <strong>{{ __('messages.Cancellation Reason') }}：</strong>
                                                {{ $booking->cancellation_reason ?? __('messages.None') }}
                                                <div class="mt-1 text-xs">
                                                    {{ __('messages.Cancelled At') }}：{{ $booking->cancelled_at->format('Y-m-d H:i') }}
                                                    （{{ $booking->cancelled_by === 'user' ? __('messages.Cancelled by User') : __('messages.Cancelled by Admin') }}）
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Actions -->
                                        <div class="flex gap-2 mt-4">
                                            <a 
                                                href="{{ route('frontend.events.show', $booking->event) }}" 
                                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                                            >
                                                {{ __('messages.View Event Details') }}
                                            </a>

                                            @if($booking->canBeCancelled())
                                                <form 
                                                    method="POST" 
                                                    action="{{ route('frontend.bookings.cancel', $booking) }}"
                                                    class="inline"
                                                    id="cancel-form-{{ $booking->id }}"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="submit" 
                                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm"
                                                        onclick="return confirm('{{ addslashes(__('messages.Are you sure you want to cancel this booking?')) }}')"
                                                    >
                                                        {{ __('messages.Cancel Booking') }}
                                                    </button>
                                                </form>
                                            @endif

                                            @if($booking->status === \App\Enums\BookingStatus::PENDING)
                                                <span class="px-4 py-2 bg-gray-100 text-gray-500 rounded-md text-sm cursor-not-allowed">
                                                    {{ __('messages.Waiting for approval') }}
                                                </span>
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
                        <p class="text-lg mb-4">{{ __('messages.No booking records') }}</p>
                        <a href="{{ route('frontend.events.index') }}" class="inline-block px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            {{ __('messages.Browse Events') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

