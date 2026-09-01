<?php

namespace App\Http\Middleware;

use App\Models\PurchaseInvoice;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class CheckLastPurchaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && ! session()->has('purchase_check_done')) {
            session()->put('purchase_check_done', true);

            $lastPurchase = PurchaseInvoice::latest('created_at')->first();

            if (! $lastPurchase || $lastPurchase->created_at->lt(now()->subDays(20))) {
                Notification::make()
                    ->title('Attenzione: Nessuna fattura provvigionale recente')
                    ->body('L\'ultima fattura provvigionale inserita risale a più di 20 giorni fa.')
                    ->warning()
                    ->persistent()
                    ->send();
            }
        }

        return $next($request);
    }
}
