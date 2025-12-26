<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    protected function exportCsv()
    {
        $bookings = $this->getFilteredTableQuery()->with(['event', 'user'])->get();

        $filename = 'bookings_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Event', 'User Name', 'User Email', 'Participants', 'Status', 'Booked At', 'Notes']);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->event?->title ?? 'N/A',
                    $booking->user?->name ?? 'N/A',
                    $booking->user?->email ?? 'N/A',
                    $booking->participants_count,
                    $booking->status->label(), // Enum 的 label() 方法返回中文标签
                    $booking->created_at->format('Y-m-d H:i:s'),
                    $booking->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

