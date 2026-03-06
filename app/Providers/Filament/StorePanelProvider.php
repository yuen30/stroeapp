<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Awcodes\Gravatar\GravatarPlugin;
use Awcodes\Gravatar\GravatarProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color as FilamentColor;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leek\FilamentDiceBear\Enums\DiceBearStyle;
use Leek\FilamentDiceBear\DiceBearPlugin;
use Openplain\FilamentShadcnTheme\Color as ShadcnColor;

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
            ->defaultAvatarProvider(GravatarProvider::class)
            ->plugins([
                DiceBearPlugin::make()
                    ->style(DiceBearStyle::Adventurer),
                GravatarPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Dashboard Widgets
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\SalesChartWidget::class,
                \App\Filament\Widgets\TopProductsChartWidget::class,
                \App\Filament\Widgets\StockStatusChartWidget::class,
                \App\Filament\Widgets\PaymentStatusChartWidget::class,
                \App\Filament\Widgets\RecentSaleOrdersWidget::class,
                \App\Filament\Widgets\LowStockProductsWidget::class,
                \App\Filament\Widgets\PendingPurchaseOrdersWidget::class,
                \App\Filament\Widgets\OutstandingPaymentsWidget::class,
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
