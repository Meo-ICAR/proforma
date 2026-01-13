<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /** Bootstrap any application services. */

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forza la generazione dell'URL per la notifica di reset
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return route('filament.admin.auth.password-reset.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
