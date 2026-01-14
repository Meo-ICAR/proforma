<?php

namespace App\Providers\Filament;

use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;  // Importante per usare Blade::render
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;  // Già che ci sei, servirà anche questo per Str::random
use Illuminate\View\Middleware\ShareErrorsFromSession;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        Event::listen(SocialiteWasCalled::class, [
            MicrosoftExtendSocialite::class,
            'handle',
        ]);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->favicon(asset('favicon.ico'))
            // ->search()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration()
            ->passwordReset()
            //   ->emailVerification()
            ->authGuard('web')  // Make sure this is set to 'web'
            ->authPasswordBroker('users')  // Make sure this is set to 'users'
            ->emailChangeVerification()
            ->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                //   AccountWidget::class,
                //   FilamentInfoWidget::class,
            ])
            ->pages([
                \App\Filament\Pages\Manuale::class,
                // ... other pages
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
            ->plugins([
                FilamentSocialitePlugin::make()
                    ->providers([
                        Provider::make('microsoft')
                            ->color('gray')  // or 'gray' for a lighter gray
                            ->label('Microsoft')
                    ])
                    ->registration(true)  // Abilita la registrazione automatica per nuovi utenti
                    // Questo forza il plugin a mostrare i bottoni in entrambe le pagine
                    //  ->showNotAssociatedMessage(true)
                    ->createUserUsing(function (string $provider, $oauthUser, $plugin) {
                        // Logica personalizzata per creare l'utente
                        return User::create([
                            'name' => $oauthUser->getName(),
                            'email' => $oauthUser->getEmail(),
                            'password' => null,  // Password nullable obbligatoria per Socialite
                            'avatar_url' => $oauthUser->getAvatar(),  // Salva l'URL di Google
                            'email_verified_at' => now(),  // Google certifica l'email, quindi la segniamo come verificata
                            'password' => Hash::make(Str::random(32)),  // Password casuale sicura
                        ]);
                    }),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
