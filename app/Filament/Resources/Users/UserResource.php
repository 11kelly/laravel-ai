<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = '用户管理';

    protected static ?string $modelLabel = '用户';

    protected static ?string $pluralModelLabel = '用户';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        // 清理密码字段 - 如果为空则不更新
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('姓名')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('profile_completed')
                    ->label('资料完善')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('注册时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('最后更新')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('profile_completed')
                    ->label('资料完善')
                    ->boolean()
                    ->trueLabel('已完善')
                    ->falseLabel('未完善')
                    ->native(false),

                Tables\Filters\Filter::make('created_at')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('删除用户')
                    ->modalDescription(function (User $record) {
                        if ($record->hasBookings()) {
                            return "此用户有 {$record->getBookingsCount()} 个预约记录，无法删除。如需删除，请先取消或删除相关预约记录。";
                        }
                        return '确定要删除这个用户吗？此操作不可撤销。';
                    })
                    ->modalSubmitActionLabel(function (User $record) {
                        return $record->hasBookings() ? '无法删除' : '确定删除';
                    })
                    ->visible(function (User $record) {
                        return $record->canBeDeleted();
                    })
                    ->action(function (User $record) {
                        if (!$record->canBeDeleted()) {
                            throw new \Exception("无法删除有预约记录的用户");
                        }
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('批量删除用户')
                        ->modalDescription(function ($records) {
                            $hasBookings = $records->filter(fn (User $record) => $record->hasBookings());
                            if ($hasBookings->isNotEmpty()) {
                                return "选中的 {$hasBookings->count()} 个用户中有预约记录，无法删除。请先处理相关预约记录。";
                            }
                            return '确定要删除选中的用户吗？此操作不可撤销。';
                        })
                        ->modalSubmitActionLabel(function ($records) {
                            return $records->filter(fn (User $record) => $record->hasBookings())->isNotEmpty()
                                ? '无法删除'
                                : '确定删除';
                        })
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            $deletableRecords = $records->filter(fn (User $record) => $record->canBeDeleted());
                            $undeletableRecords = $records->filter(fn (User $record) => !$record->canBeDeleted());

                            if ($undeletableRecords->isNotEmpty()) {
                                throw new \Exception("无法删除有预约记录的用户，请先处理相关预约");
                            }

                            $deletableRecords->each->delete();
                        }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
