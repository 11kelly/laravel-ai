<?php

declare(strict_types=1);

/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends Page
{
    // 定义该页面的基本属性
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = '个人资料';
    protected static ?string $title = '个人资料管理';
    protected static ?string $slug = 'profile';

    // 设置在菜单中的排序
    protected static ?int $navigationSort = 100;

    // 使用默认的 Filament 页面视图
    protected string $view = 'filament.pages.auth.edit-profile';

    public ?array $data = [];

    /**
     * 初始化表单数据
     */
    public function mount(): void
    {
        $user = Auth::user();

        if ($user) {
            $this->form->fill([
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }
    }

    /**
     * 定义表单结构
     */
    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('基本信息')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('姓名')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('电子邮箱')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique('users', 'email', ignorable: Auth::user()),
                    ]),

                Section::make('修改密码')
                    ->description('如果不修改密码，请留空。')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('当前密码')
                            ->password()
                            ->revealable()
                            ->requiredWith('password')
                            ->rule('current_password:admin'), // 验证当前密码是否正确

                        Forms\Components\TextInput::make('password')
                            ->label('新密码')
                            ->password()
                            ->revealable()
                            ->live(debounce: 500)
                            ->rule(Password::default()),

                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->label('确认新密码')
                            ->password()
                            ->revealable()
                            ->requiredWith('password')
                            ->same('password')
                            ->visible(fn($get) => filled($get('password'))),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * 保存修改
     */
    public function save(): void
    {
        $data = $this->form->getState();
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (filled($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        // 如果修改了密码，清空表单中的密码字段
        if (filled($data['password'])) {
            $this->data['current_password'] = null;
            $this->data['password'] = null;
            $this->data['passwordConfirmation'] = null;
        }

        Notification::make()
            ->success()
            ->title('保存成功')
            ->body('您的个人资料已更新。')
            ->send();
    }
}
