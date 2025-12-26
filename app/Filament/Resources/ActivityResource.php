<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Actions;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = '活动管理';

    protected static ?string $modelLabel = '活动';

    protected static ?string $pluralModelLabel = '活动';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('基本信息')
                    ->schema([
                        FormComponents\TextInput::make('title')
                            ->label('活动标题')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set, ?string $state) => $set('slug', Str::slug($state) . '-' . Str::random(6))),

                        FormComponents\TextInput::make('slug')
                            ->label('URL 标识')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        FormComponents\Textarea::make('description')
                            ->label('简短描述')
                            ->rows(3)
                            ->maxLength(500),

                        FormComponents\RichEditor::make('content')
                            ->label('详细内容')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('图片')
                    ->schema([
                        FormComponents\FileUpload::make('cover_image')
                            ->label('封面图片')
                            ->image()
                            ->disk('public')
                            ->directory('activities/covers')
                            ->visibility('public')
                            ->imageEditor(),

                        FormComponents\FileUpload::make('gallery')
                            ->label('图片集')
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('activities/gallery')
                            ->visibility('public')
                            ->reorderable(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('时间与地点')
                    ->schema([
                        FormComponents\DateTimePicker::make('start_time')
                            ->label('开始时间')
                            ->required()
                            ->native(false),

                        FormComponents\DateTimePicker::make('end_time')
                            ->label('结束时间')
                            ->required()
                            ->native(false)
                            ->after('start_time'),

                        FormComponents\DateTimePicker::make('registration_deadline')
                            ->label('报名截止时间')
                            ->native(false)
                            ->before('start_time'),

                        FormComponents\TextInput::make('location')
                            ->label('地点名称')
                            ->required()
                            ->maxLength(255),

                        FormComponents\Textarea::make('address')
                            ->label('详细地址')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('名额与状态')
                    ->schema([
                        FormComponents\TextInput::make('capacity')
                            ->label('容量上限')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(50),

                        FormComponents\TextInput::make('booked_count')
                            ->label('已预约人数')
                            ->numeric()
                            ->default(0)
                            ->disabled(),

                        FormComponents\Select::make('status')
                            ->label('发布状态')
                            ->options([
                                'draft' => '草稿',
                                'published' => '已发布',
                                'cancelled' => '已取消',
                            ])
                            ->default('draft')
                            ->required(),

                        FormComponents\Toggle::make('is_featured')
                            ->label('推荐活动')
                            ->default(false),
                    ])
                    ->columns(2),

                FormComponents\Hidden::make('created_by')
                    ->default(fn () => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('封面')
                    ->getStateUsing(fn (Activity $record): ?string => $record->cover_image 
                        ? asset('storage/' . $record->cover_image)
                        : null)
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-activity.svg')),

                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('开始时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('地点')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('容量')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booked_count')
                    ->label('已预约')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => '草稿',
                        'published' => '已发布',
                        'cancelled' => '已取消',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('推荐')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime('Y-m-d')
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

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('推荐活动'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
