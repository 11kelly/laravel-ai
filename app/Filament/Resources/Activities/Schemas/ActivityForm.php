<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->label('活动封面')
                    ->image()
                    ->imageEditor()
                    ->directory('activities')
                    ->disk('public')
                    ->visibility('public')
                    ->columnSpanFull()
                    ->helperText('建议尺寸: 1200x600 px'),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $state, callable $set) {
                        $set('slug', \Illuminate\Support\Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->rows(4),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('NT$')
                    ->placeholder('0 表示免费'),
                TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        if ($state) {
                            $set('available_slots', (int) $state);
                        }
                    }),
                TextInput::make('available_slots')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('创建时自动设置为容量值，预约时自动扣减'),
                DateTimePicker::make('start_date')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d H:i')
                    ->helperText('活动开始时间'),
                DateTimePicker::make('end_date')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d H:i')
                    ->after('start_date')
                    ->helperText('活动结束时间，必须晚于开始时间'),
                Select::make('status')
                    ->required()
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'cancelled' => '已取消',
                        'completed' => '已完成',
                    ])
                    ->default('draft')
                    ->helperText('草稿状态不会显示给用户'),
            ]);
    }
}
