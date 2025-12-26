<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = '预约管理';

    protected static ?string $modelLabel = '预约';

    protected static ?string $pluralModelLabel = '预约';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('预约信息')
                    ->schema([
                        FormComponents\TextInput::make('booking_code')
                            ->label('预约编号')
                            ->disabled(),

                        FormComponents\Select::make('user_id')
                            ->label('用户')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->searchable(),

                        FormComponents\Select::make('activity_id')
                            ->label('活动')
                            ->relationship('activity', 'title')
                            ->disabled()
                            ->searchable(),

                        FormComponents\TextInput::make('participants')
                            ->label('参与人数')
                            ->numeric()
                            ->disabled(),

                        FormComponents\Select::make('status')
                            ->label('状态')
                            ->options([
                                'confirmed' => '已确认',
                                'cancelled' => '已取消',
                                'completed' => '已完成',
                            ])
                            ->required(),

                        FormComponents\Textarea::make('remarks')
                            ->label('备注')
                            ->disabled()
                            ->columnSpanFull(),

                        FormComponents\Textarea::make('cancellation_reason')
                            ->label('取消原因')
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record?->status === 'cancelled'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('预约编号')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('activity.title')
                    ->label('活动')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('participants')
                    ->label('人数')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->colors([
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                        'info' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => '已确认',
                        'cancelled' => '已取消',
                        'completed' => '已完成',
                        default => $state,
                    }),

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
                        'confirmed' => '已确认',
                        'cancelled' => '已取消',
                        'completed' => '已完成',
                    ]),

                Tables\Filters\SelectFilter::make('activity')
                    ->label('活动')
                    ->relationship('activity', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
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
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
