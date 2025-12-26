<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace App\Filament\Resources;

use App\Models\Activity;
use App\Filament\Resources\ActivityResource\Pages;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static string | \UnitEnum | null $navigationGroup = '活动管理';

    protected static ?string $label = '活动';

    protected static ?string $pluralLabel = '活动';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('基本信息')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('活动标题')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->label('活动详情')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('current_remote_images')
                            ->label('当前外部图片预览')
                            ->content(fn($record) => new \Illuminate\Support\HtmlString(
                                collect($record?->images ?? [])
                                    ->filter(fn($img) => str_starts_with($img, 'http'))
                                    ->map(fn($img) => "<img src='{$img}' class='h-20 w-20 object-cover rounded-lg inline-block mr-2 shadow-sm'>")
                                    ->implode('')
                            ))
                            ->visible(fn($record) => collect($record?->images ?? [])->contains(fn($img) => str_starts_with($img, 'http')))
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('images')
                            ->label('活动图片上传')
                            ->image()
                            ->multiple()
                            ->disk('public')
                            ->directory('activities')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('时间与容量')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_at')
                            ->label('开始时间')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_at')
                            ->label('结束时间')
                            ->required(),
                        Forms\Components\TextInput::make('capacity')
                            ->label('总名额')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->live()
                            ->afterStateUpdated(fn(Set $set, $state) => $set('remaining_spots', $state)),
                        Forms\Components\TextInput::make('remaining_spots')
                            ->label('剩余名额')
                            ->numeric()
                            ->required()
                            ->readOnly(),
                        Forms\Components\Select::make('status')
                            ->label('状态')
                            ->options([
                                'draft' => '草稿',
                                'published' => '已发布',
                                'closed' => '已关闭',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('缩略图')
                    ->circular()
                    ->stacked()
                    ->disk(null)
                    ->state(fn($record): array => collect($record->images ?? [])
                        ->map(fn($image) => str_starts_with($image, 'http') ? $image : \Illuminate\Support\Facades\Storage::disk('public')->url($image))
                        ->toArray())
                    ->grow(false)
                    ->limit(3),
                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_at')
                    ->label('时间')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_spots')
                    ->label('名额 (剩余/总)')
                    ->formatStateUsing(fn($record) => "{$record->remaining_spots} / {$record->capacity}")
                    ->badge()
                    ->color(fn($state, $record) => $record->remaining_spots > 0 ? 'success' : 'danger'),
                Tables\Columns\SelectColumn::make('status')
                    ->label('状态')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'closed' => '已关闭',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'closed' => '已关闭',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
