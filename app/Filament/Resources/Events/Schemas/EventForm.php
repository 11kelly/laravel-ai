<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('基本信息')
                    ->schema([
                        TextInput::make('title')
                            ->label('活动标题')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('活动描述')
                            ->rows(5)
                            ->columnSpanFull(),

                        FileUpload::make('image')
                            ->label('活动图片')
                            ->image()
                            ->maxSize(5120)
                            ->directory('events')
                            ->disk('public')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('时间与地点')
                    ->schema([
                        DateTimePicker::make('start_time')
                            ->label('开始时间')
                            ->required()
                            ->native(false)
                            ->seconds(false),

                        DateTimePicker::make('end_time')
                            ->label('结束时间')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->after('start_time'),

                        DateTimePicker::make('booking_deadline')
                            ->label('预约截止时间')
                            ->native(false)
                            ->seconds(false)
                            ->before('start_time'),

                        TextInput::make('location')
                            ->label('活动地点')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('参与人数')
                    ->schema([
                        TextInput::make('max_participants')
                            ->label('最大参与人数')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(0),

                        TextInput::make('current_participants')
                            ->label('当前参与人数')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('此字段由系统自动维护'),
                    ])
                    ->columns(2),

                Section::make('状态')
                    ->schema([
                        Select::make('status')
                            ->label('状态')
                            ->required()
                            ->options([
                                'draft' => '草稿',
                                'published' => '已发布',
                                'cancelled' => '已取消',
                            ])
                            ->default('draft')
                            ->native(false),
                    ]),
            ]);
    }
}
