<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Enums\ThemeMode;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use App\Filament\Auth\LoginPenilaian;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class PenilaianPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('penilaian')
            ->path('penilaian')
            ->login(LoginPenilaian::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Penilaian/Resources'), for: 'App\\Filament\\Penilaian\\Resources')
            ->discoverPages(in: app_path('Filament/Penilaian/Pages'), for: 'App\\Filament\\Penilaian\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Penilaian/Widgets'), for: 'App\\Filament\\Penilaian\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
            ->brandLogo(fn () => view('filament.penilaian.logo'))
            ->brandName(function () {
                $user = Auth::user();
                $userName = $user ? $user->name : 'Cabang';
                return 'Admin MTQ ' . $userName;
            })
            ->brandLogoHeight('3.4rem')
            ->sidebarWidth('19rem')
            ->defaultThemeMode(ThemeMode::Light)
            ->favicon(asset('images/logotgxmini.png'))
            ->breadcrumbs(false);
    }
}
