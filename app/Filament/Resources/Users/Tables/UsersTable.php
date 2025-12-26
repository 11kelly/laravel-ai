<?php

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                ImageColumn::make('avatar')
                    ->label('头像')
                    ->circular()
                    ->disk('public'),

                TextColumn::make('name')
                    ->label('姓名')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label('邮箱')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage('邮箱已复制'),

                TextColumn::make('phone')
                    ->label('手机号')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bookings_count')
                    ->label('预约数量')
                    ->counts('bookings')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state < 5 => 'success',
                        default => 'warning',
                    }),

                TextColumn::make('email_verified_at')
                    ->label('邮箱验证')
                    ->dateTime()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state): string => $state ? '已验证' : '未验证'),

                TextColumn::make('created_at')
                    ->label('注册时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('email_verified')
                    ->label('邮箱验证状态')
                    ->query(function (Builder $query): Builder {
                        return $query->whereNotNull('email_verified_at');
                    })
                    ->toggle(),

                Filter::make('email_unverified')
                    ->label('未验证邮箱')
                    ->query(function (Builder $query): Builder {
                        return $query->whereNull('email_verified_at');
                    })
                    ->toggle(),
            ])
            ->actions([
                // 不显示编辑和删除操作
            ])
            ->bulkActions([
                // 不显示批量操作
            ])
            ->defaultSort('created_at', 'desc');
    }
}
