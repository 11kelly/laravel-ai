<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('封面')
                    ->circular()
                    ->disk('public')
                    ->visibility('public'),
                TextColumn::make('title')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (mb_strlen($state, 'UTF-8') <= 30) {
                            return null;
                        }
                        // 清理UTF-8字符以避免Js::from()错误
                        $state = self::sanitizeForJavaScript($state);
                        // 对于过长的文本，使用UTF-8安全的截断
                        if (mb_strlen($state, 'UTF-8') > 50) {
                            $state = self::truncateUtf8String($state, 50);
                        }
                        return $state;
                    }),
                TextColumn::make('price')
                    ->money('TWD')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => $state > 0 ? 'success' : 'gray'),
                TextColumn::make('capacity')
                    ->numeric()
                    ->sortable()
                    ->label('容量'),
                TextColumn::make('available_slots')
                    ->numeric()
                    ->sortable()
                    ->label('可用名额')
                    ->badge()
                    ->color(fn (string $state): string => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('bookings_count')
                    ->label('已预约')
                    ->getStateUsing(fn ($record) => $record->bookings()->count())
                    ->badge()
                    ->color('info'),
                TextColumn::make('start_date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->label('开始时间'),
                TextColumn::make('end_date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->label('结束时间'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => '草稿',
                        'published' => '已发布',
                        'cancelled' => '已取消',
                        'completed' => '已完成',
                        default => $state,
                    }),
                TextColumn::make('creator.name')
                    ->label('创建者')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'cancelled' => '已取消',
                        'completed' => '已完成',
                    ])
                    ->label('状态'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * 清理字符串以确保JavaScript兼容性
     */
    private static function sanitizeForJavaScript(string $string): string
    {
        // 首先确保是有效的UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', 'auto');
        }

        // 移除真正的控制字符，但保留UTF-8多字节序列
        $cleaned = '';
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            $char = $string[$i];
            $ord = ord($char);

            // 跳过真正的控制字符
            if ($ord >= 0x00 && $ord <= 0x1F || $ord === 0x7F) {
                continue;
            }

            // 对于0x80-0x9F范围，检查是否是UTF-8多字节序列的开始
            if ($ord >= 0x80 && $ord <= 0x9F) {
                // 如果是UTF-8多字节序列的开始或中间字节，保留它
                if (($ord & 0xC0) === 0xC0 || ($ord & 0xC0) === 0x80) {
                    $cleaned .= $char;
                }
                // 否则跳过（真正的C1控制字符）
            } else {
                $cleaned .= $char;
            }
        }

        return $cleaned;
    }

    /**
     * UTF-8安全的字符串截断
     */
    private static function truncateUtf8String(string $string, int $maxLength): string
    {
        if (mb_strlen($string, 'UTF-8') <= $maxLength) {
            return $string;
        }

        // 使用UTF-8安全的截断
        $truncated = mb_substr($string, 0, $maxLength, 'UTF-8');

        // 确保截断后的字符串仍然是有效的UTF-8
        if (!mb_check_encoding($truncated, 'UTF-8')) {
            // 如果截断破坏了UTF-8序列，尝试截断更少字符
            $truncated = mb_substr($string, 0, $maxLength - 1, 'UTF-8');
        }

        return $truncated . '...';
    }
}
