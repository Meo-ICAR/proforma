<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentica le chiamate del bridge generico consumato da UnicoBPM (routes/api.php)
 * tramite una chiave condivisa nell'header X-Api-Key, coerentemente con il pattern
 * già usato per l'autenticazione verso l'API di MediaFacile.
 */
class VerifyBpmApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.bpm.api_key');

        if (empty($expectedKey)) {
            Log::error('Bridge BPM disabilitato: services.bpm.api_key (BPM_BRIDGE_API_KEY) non configurata.');

            abort(503, 'Servizio non configurato.');
        }

        $providedKey = (string) $request->header('X-Api-Key', '');

        if (! hash_equals($expectedKey, $providedKey)) {
            Log::warning('Accesso al bridge BPM rifiutato: chiave API mancante o non valida.', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            abort(401, 'Non autorizzato.');
        }

        return $next($request);
    }
}
