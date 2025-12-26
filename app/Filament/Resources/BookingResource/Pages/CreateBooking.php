<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Exceptions\BookingException;
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

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        try {
            // Validate business rules through BookingService before creation
            $user = User::findOrFail($data['user_id']);
            $event = Event::findOrFail($data['event_id']);

            // Use BookingService to validate (it will throw BookingException if invalid)
            // We'll catch it and halt the creation
            $bookingService = app(BookingService::class);
            
            // Check if booking already exists
            $existingBooking = Booking::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existingBooking) {
                Notification::make()
                    ->title('预约失败')
                    ->body('您已经预约过此活动')
                    ->danger()
                    ->send();

                $this->halt();
                return;
            }

            // Check other business rules
            if (! $event->is_published) {
                Notification::make()
                    ->title('预约失败')
                    ->body('此活动尚未发布')
                    ->danger()
                    ->send();

                $this->halt();
                return;
            }

            if ($event->isFull()) {
                Notification::make()
                    ->title('预约失败')
                    ->body('活动名额已满')
                    ->danger()
                    ->send();

                $this->halt();
                return;
            }

            if ($event->start_time <= now()) {
                Notification::make()
                    ->title('预约失败')
                    ->body('活动已开始，无法预约')
                    ->danger()
                    ->send();

                $this->halt();
                return;
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('预约失败')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function handleRecordCreation(array $data): Booking
    {
        try {
            // At this point, validation has passed in beforeCreate()
            // Use BookingService to create the booking (it will handle all business logic)
            $user = User::findOrFail($data['user_id']);
            $event = Event::findOrFail($data['event_id']);

            $bookingService = app(BookingService::class);
            $booking = $bookingService->createBooking(
                $user,
                $event,
                $data['notes'] ?? null
            );

            Notification::make()
                ->title('预约成功')
                ->success()
                ->send();

            return $booking;
        } catch (BookingException $e) {
            // This should rarely happen if beforeCreate() validation passed
            // But handle it just in case (e.g., race conditions)
            Notification::make()
                ->title('预约失败')
                ->body($e->getMessage())
                ->danger()
                ->send();

            // Re-throw to prevent record creation
            throw $e;
        }
    }
}

