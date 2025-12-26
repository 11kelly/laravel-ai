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
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('活动标题')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('URL 标识')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->label('活动描述')
                    ->rows(4)
                    ->columnSpanFull(),

                FileUpload::make('image_path')
                    ->label('活动图片')
                    ->image()
                    ->directory('events')
                    ->disk('public')
                    ->maxSize(5120)
                    ->imageEditor()
                    ->columnSpanFull(),

                DateTimePicker::make('start_time')
                    ->label('开始时间')
                    ->required()
                    ->native(false),

                DateTimePicker::make('end_time')
                    ->label('结束时间')
                    ->required()
                    ->native(false)
                    ->after('start_time'),

                TextInput::make('capacity')
                    ->label('最大参与人数')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(0),

                Select::make('status')
                    ->label('状态')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'cancelled' => '已取消',
                    ])
                    ->default('draft')
                    ->required(),
            ]);
    }
}
