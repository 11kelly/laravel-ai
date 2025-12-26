<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Filesystem\FilesystemAdapter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = '活动管理';

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        return $user?->canAccessPanel(Filament::getCurrentOrDefaultPanel()) ?? false;
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()->user();

        return ($user?->isAdmin() ?? false) || ($user?->isOperator() ?? false);
    }

    public static function canEdit($record): bool
    {
        $user = Filament::auth()->user();

        return ($user?->isAdmin() ?? false) || ($user?->isOperator() ?? false);
    }

    public static function canDelete($record): bool
    {
        $user = Filament::auth()->user();

        return ($user?->isAdmin() ?? false) || ($user?->isOperator() ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('标题')
                    ->required()
                    ->maxLength(255),
                Textarea::make('summary')
                    ->label('摘要')
                    ->rows(3)
                    ->maxLength(1000),
                Textarea::make('description')
                    ->label('详情')
                    ->rows(8),

                DateTimePicker::make('starts_at')
                    ->label('开始时间')
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('结束时间'),
                TextInput::make('timezone')
                    ->label('时区')
                    ->default('UTC')
                    ->required(),
                TextInput::make('location')
                    ->label('地点')
                    ->maxLength(255),

                TextInput::make('capacity')
                    ->label('容量')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Select::make('status')
                    ->label('状态')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'archived' => '已归档',
                    ])
                    ->required()
                    ->default('draft'),

                DateTimePicker::make('published_at')
                    ->label('发布时间')
                    ->helperText('到达该时间后，活动才会在前台可见并允许预约。')
                    ->visible(fn (callable $get): bool => $get('status') === 'published'),

                Repeater::make('images')
                    ->label('活动图片')
                    ->relationship()
                    ->schema([
                        FileUpload::make('path')
                            ->label('图片')
                            ->disk('public')
                            ->directory('activity-images')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('排序')
                            ->numeric()
                            ->default(0),
                    ])
                    ->orderColumn('sort_order')
                    ->maxItems(10)
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['disk'] = 'public';
                        $data['created_by'] = Filament::auth()->id();
                        $data['created_at'] = now();

                        /** @var FilesystemAdapter $disk */
                        $disk = Storage::disk('public');
                        $data['mime_type'] = $disk->mimeType($data['path']) ?: 'application/octet-stream';
                        $data['size_bytes'] = (int) ($disk->size($data['path']) ?: 0);

                        return $data;
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                        $data['disk'] = 'public';

                        /** @var FilesystemAdapter $disk */
                        $disk = Storage::disk('public');
                        $data['mime_type'] = $disk->mimeType($data['path']) ?: 'application/octet-stream';
                        $data['size_bytes'] = (int) ($disk->size($data['path']) ?: 0);

                        return $data;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('title')->label('标题')->searchable(),
                TextColumn::make('starts_at')->label('开始')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('capacity')->label('容量')->sortable(),
                TextColumn::make('booked_count')->label('已预约')->sortable(),
                TextColumn::make('status')->label('状态')->sortable(),
                TextColumn::make('published_at')->label('发布时间')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('publish')
                    ->label('发布')
                    ->visible(fn (Activity $record): bool => $record->status !== 'published')
                    ->requiresConfirmation()
                    ->action(function (Activity $record): void {
                        $record->forceFill([
                            'status' => 'published',
                            'published_at' => $record->published_at ?? now(),
                            'updated_by' => Filament::auth()->id(),
                        ])->save();

                        Notification::make()
                            ->title('已发布')
                            ->body('活动已发布；到达发布时间后前台可预约。')
                            ->success()
                            ->send();
                    }),
                Action::make('unpublish')
                    ->label('下架')
                    ->visible(fn (Activity $record): bool => $record->status === 'published')
                    ->requiresConfirmation()
                    ->action(function (Activity $record): void {
                        $record->forceFill([
                            'status' => 'draft',
                            'published_at' => null,
                            'updated_by' => Filament::auth()->id(),
                        ])->save();

                        Notification::make()
                            ->title('已下架')
                            ->body('活动已下架，前台不可见且不可预约。')
                            ->success()
                            ->send();
                    }),
                Action::make('archive')
                    ->label('归档')
                    ->visible(fn (Activity $record): bool => $record->status !== 'archived')
                    ->requiresConfirmation()
                    ->action(function (Activity $record): void {
                        $record->forceFill([
                            'status' => 'archived',
                            'updated_by' => Filament::auth()->id(),
                        ])->save();

                        Notification::make()
                            ->title('已归档')
                            ->body('活动已归档，前台不可见且不可预约。')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('删除')
                    ->visible(fn (Activity $record): bool => ! $record->bookings()->exists())
                    ->requiresConfirmation()
                    ->successNotificationTitle('已删除'),
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


