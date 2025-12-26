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
use App\Services\BookingService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $booking = $this->record;
        $oldStatus = $booking->status;
        $newStatus = $data['status'] ?? $oldStatus;

        // If status changed, use BookingService to handle it properly
        if ($oldStatus !== $newStatus) {
            // Validate state transitions
            $validTransitions = [
                Booking::STATUS_PENDING => [Booking::STATUS_CONFIRMED, Booking::STATUS_CANCELLED],
                Booking::STATUS_CONFIRMED => [Booking::STATUS_CANCELLED],
                Booking::STATUS_CANCELLED => [], // Cannot transition from cancelled
            ];

            if (! isset($validTransitions[$oldStatus]) || ! in_array($newStatus, $validTransitions[$oldStatus], true)) {
                Notification::make()
                    ->title('状态转换无效')
                    ->body("无法从 {$this->getStatusLabel($oldStatus)} 转换为 {$this->getStatusLabel($newStatus)}")
                    ->danger()
                    ->send();

                $this->halt();
            }

            $bookingService = app(BookingService::class);

            try {
                if ($newStatus === Booking::STATUS_CONFIRMED && $oldStatus === Booking::STATUS_PENDING) {
                    $bookingService->confirmBooking($booking);
                } elseif ($newStatus === Booking::STATUS_CANCELLED && $oldStatus !== Booking::STATUS_CANCELLED) {
                    $bookingService->cancelBooking($booking);
                }
            } catch (BookingException $e) {
                Notification::make()
                    ->title('状态更新失败')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();

                $this->halt();
            }
        }

        return $data;
    }

    /**
     * Get human-readable status label.
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            Booking::STATUS_PENDING => '待确认',
            Booking::STATUS_CONFIRMED => '已确认',
            Booking::STATUS_CANCELLED => '已取消',
            default => $status,
        };
    }
}

