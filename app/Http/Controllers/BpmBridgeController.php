<?php

namespace App\Http\Controllers;

use App\Models\Fornitore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BpmBridgeController extends Controller
{
    /**
     * Messaggio generico mostrato per qualunque fallimento dell'accesso via BPM,
     * per non rivelare a un chiamante non autorizzato se il token è invalido
     * oppure se l'utente semplicemente non è censito in questa applicazione.
     */
    protected const GENERIC_ERROR = 'Accesso non consentito.';

    public function handle(Request $request, string $subject_id)
    {
        $token = $request->query('token');
        $userEmail = $request->query('user_email');

        if (! $token || ! $userEmail) {
            Log::warning('Accesso BPM rifiutato: parametri mancanti.', [
                'ip' => $request->ip(),
                'subject_id' => $subject_id,
            ]);

            abort(403, self::GENERIC_ERROR);
        }

        $bpmBaseUrl = config('services.bpm.url');

        if (! $bpmBaseUrl) {
            Log::error('Accesso BPM fallito: BPM_URL non configurato.');

            abort(403, self::GENERIC_ERROR);
        }

        try {
            $response = Http::timeout(5)->post("{$bpmBaseUrl}/api/verify-token", [
                'token' => $token,
                'email' => $userEmail,
            ]);
        } catch (\Throwable $e) {
            Log::error('Accesso BPM fallito: errore di comunicazione con il BPM.', [
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            abort(403, self::GENERIC_ERROR);
        }

        if ($response->failed() || ! $response->json('valid')) {
            Log::warning('Accesso BPM rifiutato: token non valido o scaduto.', [
                'ip' => $request->ip(),
                'user_email' => $userEmail,
                'subject_id' => $subject_id,
            ]);

            abort(403, self::GENERIC_ERROR);
        }

        $user = User::where('email', $userEmail)->first();

        if (! $user) {
            Log::warning('Accesso BPM rifiutato: utente non censito in questa applicazione.', [
                'ip' => $request->ip(),
                'user_email' => $userEmail,
                'subject_id' => $subject_id,
            ]);

            abort(403, self::GENERIC_ERROR);
        }

        Auth::login($user);

        Log::info('Accesso BPM completato.', [
            'user_id' => $user->id,
            'subject_id' => $subject_id,
        ]);

        // $subject_id punta a un Fornitore solo quando l'accesso arriva da un contesto
        // specifico (es. scheda agente su BPM); un accesso generico ("passa a
        // quest'app" dalla dashboard di BPM) non ha un record da aprire.
        $fornitore = Fornitore::find($subject_id);

        return $fornitore
            ? redirect()->route('filament.admin.resources.fornitores.view', ['record' => $fornitore])->with('message', 'Accesso effettuato tramite BPM')
            : redirect('/admin')->with('message', 'Accesso effettuato tramite BPM');
    }
}
