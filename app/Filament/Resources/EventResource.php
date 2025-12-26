<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\EventStatus;
use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Events';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, Set $set) => 
                                $set('slug', Str::slug($state) . '-' . time())
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Date & Time')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->required()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('end_time')
                            ->required()
                            ->native(false)
                            ->after('start_time'),
                        Forms\Components\DateTimePicker::make('booking_deadline')
                            ->native(false)
                            ->before('start_time'),
                    ])->columns(3),

                Section::make('Location & Capacity')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('capacity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(100),
                        Forms\Components\TextInput::make('booked_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3),

                Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->disk('public')
                            ->directory('events')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imageEditor()
                            ->previewable()
                            ->downloadable()
                            ->openable(),
                    ]),

                Section::make('Organizer Information')
                    ->schema([
                        Forms\Components\TextInput::make('organizer_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('organizer_contact')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Additional Settings')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(EventStatus::class)
                            ->required()
                            ->default(EventStatus::DRAFT),
                        Forms\Components\Textarea::make('booking_rules')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->getStateUsing(fn ($record) => $record->cover_image_url)
                    ->url(fn ($record) => $record->cover_image_url)
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('booked_count')
                    ->numeric()
                    ->sortable()
                    ->label('Booked'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (EventStatus $state): string => $state->label())
                    ->color(fn (EventStatus $state): string => $state->color()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(EventStatus::class),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->before(function (Event $record) {
                        if ($record->confirmedBookings()->count() > 0) {
                            throw new \Exception('Cannot delete event with confirmed bookings');
                        }
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_time', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\EventResource\RelationManagers\ImagesRelationManager::class,
            \App\Filament\Resources\EventResource\RelationManagers\BookingsRelationManager::class,
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}
