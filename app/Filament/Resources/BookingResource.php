<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace App\Filament\Resources;

use App\Models\Booking;
use App\Filament\Resources\BookingResource\Pages;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static string | \UnitEnum | null $navigationGroup = '活动管理';

    protected static ?string $label = '预约记录';

    protected static ?string $pluralLabel = '预约记录';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('预约信息')
                    ->schema([
                        Forms\Components\Select::make('activity_id')
                            ->relationship('activity', 'title')
                            ->required()
                            ->disabledOn('edit'),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->disabledOn('edit'),
                        Forms\Components\TextInput::make('booking_code')
                            ->label('预约码')
                            ->required()
                            ->readOnly(),
                        Forms\Components\Select::make('status')
                            ->label('状态')
                            ->options([
                                'pending' => '待确认',
                                'confirmed' => '已确认',
                                'cancelled' => '已取消',
                                'attended' => '已签到',
                            ])
                            ->required(),
                    ])->columns(2),

                Section::make('参与者详情')
                    ->schema([
                        Forms\Components\KeyValue::make('participant_info')
                            ->label('填写信息')
                            ->keyLabel('字段名')
                            ->valueLabel('内容')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('ticket_quantity')
                            ->label('票数')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->disabledOn('edit'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity.title')
                    ->label('活动')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->searchable(),
                Tables\Columns\TextColumn::make('booking_code')
                    ->label('预约码')
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'info',
                        'attended' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('ticket_quantity')
                    ->label('人数')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('预约时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => '待确认',
                        'confirmed' => '已确认',
                        'cancelled' => '已取消',
                        'attended' => '已签到',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('confirm')
                    ->label('确认')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Booking $record) => $record->update(['status' => 'confirmed']))
                    ->visible(fn (Booking $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}

