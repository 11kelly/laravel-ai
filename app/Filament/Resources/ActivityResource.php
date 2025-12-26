<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = '活動管理';

    protected static ?string $modelLabel = '活動';

    protected static ?string $pluralModelLabel = '活動';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('活動資訊')
                    ->schema([
                        TextInput::make('title')
                            ->label('活動標題')
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('description')
                            ->label('活動描述')
                            ->columnSpanFull(),

                        FileUpload::make('cover_image')
                            ->label('封面圖片')
                            ->image()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('activities')
                            ->visibility('public')
                            ->previewable(true)
                            ->maxSize(5120),
                    ]),

                Section::make('時間與名額')
                    ->schema([
                        DateTimePicker::make('start_time')
                            ->label('開始時間')
                            ->required()
                            ->native(false),

                        DateTimePicker::make('end_time')
                            ->label('結束時間')
                            ->required()
                            ->native(false)
                            ->after('start_time'),

                        TextInput::make('capacity')
                            ->label('名額上限')
                            ->helperText('0 表示不限制')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Select::make('status')
                            ->label('狀態')
                            ->options([
                                'draft' => '草稿',
                                'published' => '已發布',
                                'cancelled' => '已取消',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),

                Hidden::make('admin_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('封面')
                    ->disk('public')
                    ->checkFileExistence(false)
                    ->circular(),

                TextColumn::make('title')
                    ->label('標題')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('開始時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('end_time')
                    ->label('結束時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('capacity')
                    ->label('名額')
                    ->formatStateUsing(fn ($state) => $state === 0 ? '不限' : $state),

                TextColumn::make('reserved_count')
                    ->label('已預約'),

                TextColumn::make('status')
                    ->label('狀態')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => '草稿',
                        'published' => '已發布',
                        'cancelled' => '已取消',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('狀態')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已發布',
                        'cancelled' => '已取消',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                Action::make('publish')
                    ->label('發布')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Activity $record) => $record->status === 'draft')
                    ->action(fn (Activity $record) => $record->update(['status' => 'published'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_time', 'desc');
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
