<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('姓名')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('profile_completed')
                    ->label('资料完善')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('注册时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('最后更新')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('profile_completed')
                    ->label('资料完善')
                    ->boolean()
                    ->trueLabel('已完善')
                    ->falseLabel('未完善')
                    ->native(false),

                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('注册开始日期'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('注册结束日期'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('删除用户')
                    ->modalDescription('确定要删除这个用户吗？此操作不可撤销。')
                    ->modalSubmitActionLabel('确定删除'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('批量删除用户')
                        ->modalDescription('确定要删除选中的用户吗？此操作不可撤销。')
                        ->modalSubmitActionLabel('确定删除'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
