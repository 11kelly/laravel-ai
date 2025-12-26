<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_reference')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->helperText('预约参考号，系统自动生成'),
                Select::make('activity_id')
                    ->relationship('activity', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('活动'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('用户'),
                Textarea::make('contact_info')
                    ->required()
                    ->columnSpanFull()
                    ->rows(3)
                    ->json()
                    ->helperText('联系信息，JSON格式'),
                Textarea::make('special_requirements')
                    ->columnSpanFull()
                    ->rows(2)
                    ->placeholder('特殊要求（如无障碍设施、饮食限制等）'),
                Select::make('status')
                    ->required()
                    ->options([
                        'pending' => '待确认',
                        'confirmed' => '已确认',
                        'cancelled' => '已取消',
                        'attended' => '已出席',
                    ])
                    ->default('pending')
                    ->helperText('预约状态'),
            ]);
    }
}
