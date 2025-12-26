<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Enums\BookingStatus;
use Filament\Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Booking ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->copyable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label('Participants')
                    ->sortable(),
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
            ])
            ->headerActions([])
            ->actions([
                Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.bookings.view', $record)),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }
}
