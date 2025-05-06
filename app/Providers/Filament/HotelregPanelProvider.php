<?php

namespace App\Providers\Filament;

use App\Filament\Auth\HotelLogin;
use App\Models\Hotel;
use App\Policies\HotelPolicy;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Gate;
use App\Filament\Widgets\WelcomeInstructions;

class HotelregPanelProvider extends PanelProvider
{

    public function widgets(): array
{
    return [
        \App\Filament\Widgets\WelcomeInstructions::class,
    ];
}


    public function panel(Panel $panel): Panel
    {
        // Register the HotelPolicy for the actions
        Gate::policy(Hotel::class, HotelPolicy::class);

        return $panel
            ->id('hotelreg')
            ->path('hotelreg')
            ->login()
            ->registration()
            ->passwordReset()
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::Violet,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Hotelreg/Resources'), for: 'App\\Filament\\Hotelreg\\Resources')
            ->discoverPages(in: app_path('Filament/Hotelreg/Pages'), for: 'App\\Filament\\Hotelreg\\Pages')
            ->pages([
                Pages\Dashboard::class,  // Add more pages as needed
            ])
            ->discoverWidgets(in: app_path('Filament/Hotelreg/Widgets'), for: 'App\\Filament\\Hotelreg\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ->brandLogo(asset('images/logo.jpg'))
            ->brandLogoHeight('50px')
            ->authGuard('hotels')
            ->widgets([
               
            ]);
    }
}
