<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Services\BookingService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordCreation(array $data): Booking
    {
        // Validate business rules through BookingService
        $user = User::findOrFail($data['user_id']);
        $event = Event::findOrFail($data['event_id']);

        $bookingService = app(BookingService::class);
        $booking = $bookingService->createBooking(
            $user,
            $event,
            $data['notes'] ?? null
        );

        // BookingService always creates bookings with PENDING status
        // Status changes should be done through EditBooking page using BookingService methods
        // This ensures all business rules are enforced

        return $booking;
    }
}

