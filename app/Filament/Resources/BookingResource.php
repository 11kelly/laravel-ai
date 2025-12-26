<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Services\BookingCancellationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Bookings';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('event_id')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('participants_count')
                    ->numeric()
                    ->required()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options(BookingStatus::class)
                    ->required()
                    ->disabled(),
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->disabled(),
                Forms\Components\Textarea::make('cancellation_reason')
                    ->rows(3)
                    ->visible(fn ($record) => $record?->status === BookingStatus::CANCELLED),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('event.title')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->copyable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('participants_count')
                    ->numeric()
                    ->sortable()
                    ->label('Participants'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (BookingStatus $state): string => $state->label())
                    ->color(fn (BookingStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Booked At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(BookingStatus::class),
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                
                // Approve pending booking
                Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Booking $record) {
                        try {
                            $record->update([
                                'status' => 'confirmed',
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Booking approved successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to approve booking')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Booking $record) => $record->status === 'pending'),
                
                // Reject pending booking
                Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Booking $record, array $data) {
                        try {
                            $service = app(BookingCancellationService::class);
                            $service->cancelBooking($record, 'admin', $data['reason']);

                            Notification::make()
                                ->title('Booking rejected successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to reject booking')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Booking $record) => $record->status === 'pending'),
                
                // Cancel confirmed booking
                Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Cancellation Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Booking $record, array $data) {
                        try {
                            $service = app(BookingCancellationService::class);
                            $service->cancelBooking($record, 'admin', $data['reason']);

                            Notification::make()
                                ->title('Booking cancelled successfully')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to cancel booking')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Booking $record) => $record->status === 'confirmed'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Actions\ExportAction::make()
                    ->exporter(\App\Filament\Exports\BookingExporter::class)
                    ->label('Export CSV'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }
}
