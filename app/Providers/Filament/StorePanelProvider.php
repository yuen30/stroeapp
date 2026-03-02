<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color as FilamentColor;
use Filament\Support\Enums\Width;
use Openplain\FilamentShadcnTheme\Color as ShadcnColor;
use Filament\Widgets\AccountWidget;
use Leek\FilamentDiceBear\DiceBearPlugin;
use Leek\FilamentDiceBear\DiceBearProvider;
use Leek\FilamentDiceBear\Enums\DiceBearStyle;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Auth\Login;

class StorePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('store')
            ->path('store')
            ->login(Login::class)
            ->resourceCreatePageRedirect('index')
            ->colors([
                'primary' => ShadcnColor::adaptive(
                    lightColor: FilamentColor::Blue,
                    darkColor: FilamentColor::Teal
                )
            ])
            // ->defaultAvatarProvider(DiceBearProvider::class)
            ->plugins([
                DiceBearPlugin::make()
                    ->style(DiceBearStyle::Adventurer),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
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
                Authenticate::class,
            ])
            ->sidebarWidth('15rem')
            ->databaseNotifications()
            ->sidebarFullyCollapsibleOnDesktop()
            ->font('Roboto')
            ->maxContentWidth(Width::Full);
    }
}
