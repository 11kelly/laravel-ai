<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = '活动管理';

    protected static ?string $modelLabel = '活动';

    protected static ?string $pluralModelLabel = '活动';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('基本信息')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('活动标题')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL 标识')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash(),

                        Forms\Components\Textarea::make('description')
                            ->label('活动描述')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->label('活动封面')
                            ->image()
                            ->disk('public')
                            ->directory('events')
                            ->maxSize(10240)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('活动详情')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('开始时间')
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('结束时间')
                            ->required()
                            ->native(false)
                            ->after('start_time'),

                        Forms\Components\TextInput::make('location')
                            ->label('活动地点')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('capacity')
                            ->label('活动容量')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0),
                    ])->columns(2),

                Section::make('发布设置')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('是否发布')
                            ->default(false)
                            ->live(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('发布时间')
                            ->native(false)
                            ->visible(fn (Get $get) => $get('is_published')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('封面')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('开始时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('地点')
                    ->searchable(),

                Tables\Columns\TextColumn::make('booked_count')
                    ->label('已预约')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('容量')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('已发布')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('发布状态')
                    ->placeholder('全部')
                    ->trueLabel('已发布')
                    ->falseLabel('未发布'),

                Tables\Filters\Filter::make('start_time')
                    ->schema([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('开始日期')
                            ->native(false),
                        Forms\Components\DatePicker::make('start_until')
                            ->label('结束日期')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                filled($data['start_from'] ?? null),
                                fn ($query) => $query->whereDate('start_time', '>=', $data['start_from'])
                            )
                            ->when(
                                filled($data['start_until'] ?? null),
                                fn ($query) => $query->whereDate('start_time', '<=', $data['start_until'])
                            );
                    }),
            ])
            ->recordActions([
                Action::make('publish')
                    ->label('发布')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Event $record) => ! $record->is_published)
                    ->action(fn (Event $record) => $record->update([
                        'is_published' => true,
                        'published_at' => now(),
                    ])),

                Action::make('unpublish')
                    ->label('下架')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Event $record) => $record->is_published)
                    ->action(fn (Event $record) => $record->update([
                        'is_published' => false,
                    ])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ]);
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}

