<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BookingExporter extends Exporter
{
    protected static ?string $model = Booking::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Booking ID'),
            ExportColumn::make('event.title')
                ->label('Event Title'),
            ExportColumn::make('event.start_time')
                ->label('Event Start Time'),
            ExportColumn::make('event.location')
                ->label('Event Location'),
            ExportColumn::make('user.name')
                ->label('User Name'),
            ExportColumn::make('user.email')
                ->label('User Email'),
            ExportColumn::make('user.phone')
                ->label('User Phone'),
            ExportColumn::make('participants_count')
                ->label('Participants'),
            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (BookingStatus $state): string => $state->label()),
            ExportColumn::make('notes')
                ->label('Notes'),
            ExportColumn::make('created_at')
                ->label('Booked At'),
            ExportColumn::make('cancelled_at')
                ->label('Cancelled At'),
            ExportColumn::make('cancelled_by')
                ->label('Cancelled By'),
            ExportColumn::make('cancellation_reason')
                ->label('Cancellation Reason'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your booking export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}

