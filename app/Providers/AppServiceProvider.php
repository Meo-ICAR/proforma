<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Notifications\ResetPassword;
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
        // Proforma possiede il proprio database: le tabelle del pacchetto si creano con il normale `php artisan migrate`
        // (e quindi anche con `migrate:fresh`, nei test). Se il database è condiviso e le crea un'altra app, UNICO_CORE_MIGRATE=false.
        if (config('database.unico_core_migrate')) {
            $this->loadMigrationsFrom(\Unico\Core\UnicoCoreServiceProvider::migrationsPath());
        }

        // Il sottotitolo delle pagine elenco occupa tutta la larghezza (di default è limitato a max-w-2xl).
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => '<style>.fi-resource-list-records-page .fi-header-subheading{max-width:none}</style>'
        );

        // Forza la generazione dell'URL per la notifica di reset
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return route('filament.admin.auth.password-reset.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
