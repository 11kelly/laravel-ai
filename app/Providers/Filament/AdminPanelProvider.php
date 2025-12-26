<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('admin')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->font('Inter')
            ->brandName('活动预约后台')
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('个人资料')
                    ->url(fn(): string => \App\Filament\Pages\Auth\EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn() => new \Illuminate\Support\HtmlString('
                    <style>
                        .fi-simple-main {
                            border-radius: 1.5rem !important;
                        }
                        .fi-btn { 
                            padding-top: 0.75rem !important; 
                            padding-bottom: 0.75rem !important;
                            font-weight: 700 !important;
                            border-radius: 0.75rem !important;
                            transition: all 0.2s !important;
                        }
                        .fi-btn:active {
                            transform: scale(0.98);
                        }
                        .fi-simple-page .fi-logo {
                            font-size: 1.5rem;
                            font-weight: 800;
                            letter-spacing: -0.025em;
                        }
                    </style>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                \App\Filament\Pages\Auth\EditProfile::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
                \App\Http\Middleware\EnsureUserIsAdmin::class,
            ]);
    }
}
