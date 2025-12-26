<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = '預約管理';

    protected static ?string $modelLabel = '預約';

    protected static ?string $pluralModelLabel = '預約';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('用戶')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->disabled(),

                Select::make('activity_id')
                    ->label('活動')
                    ->relationship('activity', 'title')
                    ->searchable()
                    ->preload()
                    ->disabled(),

                Select::make('status')
                    ->label('狀態')
                    ->options([
                        'pending' => '待確認',
                        'confirmed' => '已確認',
                        'cancelled' => '已取消',
                        'expired' => '已過期',
                    ])
                    ->required(),

                Textarea::make('remark')
                    ->label('備註')
                    ->disabled(),

                DateTimePicker::make('reserved_at')
                    ->label('預約時間')
                    ->disabled(),

                DateTimePicker::make('cancelled_at')
                    ->label('取消時間')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('用戶')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('activity.title')
                    ->label('活動')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('status')
                    ->label('狀態')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => '待確認',
                        'confirmed' => '已確認',
                        'cancelled' => '已取消',
                        'expired' => '已過期',
                        default => $state,
                    }),

                TextColumn::make('reserved_at')
                    ->label('預約時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('cancelled_at')
                    ->label('取消時間')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('狀態')
                    ->options([
                        'pending' => '待確認',
                        'confirmed' => '已確認',
                        'cancelled' => '已取消',
                        'expired' => '已過期',
                    ]),

                SelectFilter::make('activity_id')
                    ->label('活動')
                    ->relationship('activity', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('reserved_at', 'desc');
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
            'index' => Pages\ListReservations::route('/'),
            'view' => Pages\ViewReservation::route('/{record}'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
