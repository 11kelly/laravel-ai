<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\Admin;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = '管理员管理';

    public static function canViewAny(): bool
    {
        return Filament::auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return Filament::auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return Filament::auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return Filament::auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('姓名')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('邮箱')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Select::make('role')
                ->label('角色')
                ->options([
                    'admin' => '管理员',
                    'operator' => '运营',
                ])
                ->required()
                ->default('operator'),

            Toggle::make('is_active')
                ->label('启用')
                ->default(true),

            TextInput::make('password')
                ->label('密码')
                ->password()
                ->revealable()
                ->helperText('编辑时留空则不修改密码。')
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create')
                ->minLength(8),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('姓名')->searchable(),
                TextColumn::make('email')->label('邮箱')->searchable(),
                TextColumn::make('role')->label('角色')->sortable(),
                IconColumn::make('is_active')->label('启用')->boolean(),
                TextColumn::make('created_at')->label('创建时间')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(function (Admin $record): bool {
                        // 防止把自己删掉，或删到最后一个 admin 造成无法管理
                        $current = Filament::auth()->user();
                        if (! $current) {
                            return false;
                        }

                        if ((int) $record->id === (int) $current->getAuthIdentifier()) {
                            return false;
                        }

                        if ($record->isAdmin()) {
                            $adminCount = Admin::query()
                                ->where('role', '=', 'admin')
                                ->where('is_active', '=', true)
                                ->count();

                            return $adminCount > 1;
                        }

                        return true;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}


