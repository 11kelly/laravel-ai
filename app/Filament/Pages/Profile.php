<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament.pages.profile';

    protected static ?string $navigationLabel = '個人資料';

    protected static ?int $navigationSort = 100;

    protected static \UnitEnum | string | null $navigationGroup = '設定';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('基本資訊')
                    ->description('更新您的個人基本資訊')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('姓名')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        Forms\Components\TextInput::make('email')
                            ->label('電子郵件')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn () => Auth::user()->email_verified_at !== null)
                            ->helperText(fn () => Auth::user()->email_verified_at 
                                ? '電子郵件已驗證，無法修改' 
                                : '電子郵件未驗證，可以修改'),
                        Forms\Components\TextInput::make('phone')
                            ->label('電話')
                            ->tel()
                            ->maxLength(20)
                            ->helperText('請輸入您的聯絡電話'),
                    ])->columns(2),

                Section::make('頭像')
                    ->description('上傳您的個人頭像')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->label('頭像')
                            ->image()
                            ->directory('avatars')
                            ->disk('public')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->helperText('建議尺寸：200x200 像素，最大 2MB'),
                    ]),

                Section::make('密碼')
                    ->description('修改您的登入密碼（留空則不修改）')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('目前密碼')
                            ->password()
                            ->required(fn ($get) => filled($get('password')))
                            ->dehydrated(false)
                            ->helperText('修改密碼時需要輸入目前密碼'),
                        Forms\Components\TextInput::make('password')
                            ->label('新密碼')
                            ->password()
                            ->minLength(8)
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(false)
                            ->confirmed()
                            ->helperText('至少 8 個字元'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('確認新密碼')
                            ->password()
                            ->maxLength(255)
                            ->requiredWith('password')
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        // 驗證目前密碼（如果修改密碼）
        if (filled($data['password'] ?? null)) {
            if (!isset($data['current_password']) || !Hash::check($data['current_password'], $user->password)) {
                $this->form->getComponent('current_password')->addError('目前密碼不正確');
                return;
            }
        }

        // 移除不需要儲存的欄位
        unset($data['current_password'], $data['password_confirmation']);

        // 如果沒有輸入新密碼，移除 password 欄位
        if (empty($data['password'] ?? null)) {
            unset($data['password']);
        }

        // 更新用戶資料
        $user->update($data);

        // 重新填充表單
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
        ]);

        $this->notification()
            ->success()
            ->title('個人資料已更新')
            ->body('您的個人資料已成功更新。')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('儲存')
                ->submit('save'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}

