<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('姓名')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('密码')
                    ->password()
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->minLength(8)
                    ->helperText('创建时必填，编辑时留空表示不修改密码'),

                KeyValue::make('contact_info')
                    ->label('联系信息')
                    ->keyLabel('字段')
                    ->valueLabel('值')
                    ->default([])
                    ->columnSpanFull()
                    ->helperText('用户的联系信息，包括姓名、邮箱、地址、紧急联系人等'),

                Toggle::make('profile_completed')
                    ->label('资料完善')
                    ->default(false)
                    ->helperText('标记用户是否已完善个人资料'),

                TextInput::make('created_at')
                    ->label('注册时间')
                    ->disabled()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        if (is_string($state)) {
                            return \Carbon\Carbon::parse($state)->format('Y-m-d H:i:s');
                        }
                        return $state->format('Y-m-d H:i:s');
                    })
                    ->helperText('用户注册时间'),

                TextInput::make('updated_at')
                    ->label('最后更新')
                    ->disabled()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        if (is_string($state)) {
                            return \Carbon\Carbon::parse($state)->format('Y-m-d H:i:s');
                        }
                        return $state->format('Y-m-d H:i:s');
                    })
                    ->helperText('用户信息最后更新时间'),
            ]);
    }
}
