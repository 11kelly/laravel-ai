<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = '活动管理';

    protected static ?string $modelLabel = '活动';

    protected static ?string $pluralModelLabel = '活动';

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
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('short_description')
                            ->label('简短描述')
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label('详细描述')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('开始时间')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d H:i'),

                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('结束时间')
                            ->required()
                            ->native(false)
                            ->displayFormat('Y-m-d H:i')
                            ->after('start_time'),

                        Forms\Components\TextInput::make('location')
                            ->label('活动地点')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('活动图片')
                            ->image()
                            ->disk('public')
                            ->directory('activities')
                            ->visibility('public')
                            ->maxSize(2048) // 2MB (matching PHP upload_max_filesize)
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
                            ->imageEditor()
                            ->columnSpanFull(),

                        Section::make('参与人数设置')
                            ->schema([
                                Forms\Components\TextInput::make('max_participants')
                                    ->label('最大参与人数')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('0 表示无限制')
                                    ->minValue(0),

                                Forms\Components\TextInput::make('current_participants')
                                    ->label('当前参与人数')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(2),

                        Forms\Components\Select::make('status')
                            ->label('状态')
                            ->options([
                                'draft' => '草稿',
                                'published' => '已发布',
                                'cancelled' => '已取消',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\Hidden::make('created_by')
                            ->default(fn () => Auth::id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('图片')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('开始时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('结束时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('地点')
                    ->searchable(),

                Tables\Columns\TextColumn::make('current_participants')
                    ->label('当前人数')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_participants')
                    ->label('最大人数')
                    ->formatStateUsing(fn ($state) => $state == 0 ? '无限制' : $state)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('状态')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => '草稿',
                        'published' => '已发布',
                        'cancelled' => '已取消',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'cancelled' => '已取消',
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->label('編輯'),
                DeleteAction::make()
                    ->label('刪除'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('刪除'),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}

