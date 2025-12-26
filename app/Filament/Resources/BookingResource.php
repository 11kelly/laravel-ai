<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Services\BookingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = '预约管理';

    protected static ?string $modelLabel = '预约';

    protected static ?string $pluralModelLabel = '预约';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('预约信息')
                    ->schema([
                        Select::make('user_id')
                            ->label('用户')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (?Booking $record) => $record !== null),

                        Select::make('event_id')
                            ->label('活动')
                            ->relationship('event', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (?Booking $record) => $record !== null),

                        Select::make('status')
                            ->label('状态')
                            ->options([
                                Booking::STATUS_PENDING => '待确认',
                                Booking::STATUS_CONFIRMED => '已确认',
                                Booking::STATUS_CANCELLED => '已取消',
                            ])
                            ->required()
                            ->default(Booking::STATUS_PENDING),

                        Textarea::make('notes')
                            ->label('备注')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event.title')
                    ->label('活动')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Booking::STATUS_PENDING => 'warning',
                        Booking::STATUS_CONFIRMED => 'success',
                        Booking::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Booking::STATUS_PENDING => '待确认',
                        Booking::STATUS_CONFIRMED => '已确认',
                        Booking::STATUS_CANCELLED => '已取消',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('预约时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cancelled_at')
                    ->label('取消时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        Booking::STATUS_PENDING => '待确认',
                        Booking::STATUS_CONFIRMED => '已确认',
                        Booking::STATUS_CANCELLED => '已取消',
                    ]),

                Tables\Filters\SelectFilter::make('event_id')
                    ->label('活动')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label('确认')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => $record->isPending())
                    ->action(function (Booking $record) {
                        $service = app(BookingService::class);
                        $service->confirmBooking($record);
                    }),

                Action::make('cancel')
                    ->label('取消')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Booking $record) => ! $record->isCancelled())
                    ->action(function (Booking $record) {
                        $service = app(BookingService::class);
                        $service->cancelBooking($record);
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
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
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}

