<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use App\Services\BookingServiceException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var BookingService $service */
        $service = app(BookingService::class);

        $actor = auth()->user();
        if (! $actor?->isAdmin()) {
            Notification::make()->title('无权限')->danger()->send();
            $this->halt();
        }

        try {
            /** @var User $user */
            $user = User::query()->findOrFail((int) $data['user_id']);

            $booking = $service->createBookingOnBehalf(
                actor: $actor,
                user: $user,
                activityId: (int) $data['activity_id'],
                idempotencyKey: null,
                reason: $data['admin_reason'] ?? null,
                requestId: request()?->header('X-Request-Id'),
            );

            return $booking;
        } catch (BookingServiceException $e) {
            Notification::make()->title('代预约失败')->body($e->errorCode())->danger()->send();
            $this->halt();
        }

        return Booking::query()->make();
    }
}


