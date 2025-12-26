<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('预约信息')
                    ->schema([
                        Select::make('event_id')
                            ->label('活动')
                            ->relationship('event', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Select::make('user_id')
                            ->label('用户')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Select::make('status')
                            ->label('状态')
                            ->required()
                            ->options([
                                'pending' => '待确认',
                                'confirmed' => '已确认',
                                'cancelled' => '已取消',
                            ])
                            ->default('pending')
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('备注')
                    ->schema([
                        Textarea::make('notes')
                            ->label('用户备注')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('admin_notes')
                            ->label('管理员备注')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
