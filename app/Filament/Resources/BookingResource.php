<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\BookingServiceException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = '预约管理';

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        return $user?->canAccessPanel(Filament::getCurrentOrDefaultPanel()) ?? false;
    }

    public static function canCreate(): bool
    {
        return Filament::auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('activity_id')
                    ->label('活动')
                    ->relationship('activity', 'title')
                    ->searchable()
                    ->required(),
                Select::make('user_id')
                    ->label('用户')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->required(),
                Textarea::make('admin_reason')
                    ->label('代预约原因（审计用，可选）')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('activity.title')->label('活动')->searchable(),
                TextColumn::make('user.email')->label('用户')->searchable(),
                TextColumn::make('status')->label('状态')->sortable(),
                TextColumn::make('booked_at')->label('预约时间')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('cancelled_at')->label('取消时间')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => '已预约',
                        'cancelled' => '已取消',
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn () => Filament::auth()->user()?->isAdmin() ?? false),

                Action::make('cancel')
                    ->label('代取消')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn (Booking $record) => (Filament::auth()->user()?->isAdmin() ?? false) && $record->status === 'active')
                    ->form([
                        Textarea::make('reason')
                            ->label('原因（审计用）')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (Booking $record, array $data) {
                        /** @var BookingService $service */
                        $service = app(BookingService::class);

                        try {
                            $service->cancelBooking(
                                actor: Filament::auth()->user(),
                                bookingId: (int) $record->id,
                                reason: $data['reason'] ?? null,
                                requestId: request()?->header('X-Request-Id'),
                                impersonatedUser: $record->user,
                            );
                            Notification::make()->title('已代取消预约')->success()->send();
                        } catch (BookingServiceException $e) {
                            Notification::make()->title('取消失败')->body($e->errorCode())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}


