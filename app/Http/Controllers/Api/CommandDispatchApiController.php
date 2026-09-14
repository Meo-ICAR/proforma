<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RunArtisanCommandJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Bridge che permette a un sistema esterno di scheduling (es. UnicoBPM) di
 * lanciare i comandi Artisan/servizi di questa app senza accesso diretto al
 * server. Solo i comandi elencati in config('schedulable_commands') possono
 * essere eseguiti, e solo con le opzioni lì dichiarate.
 */
class CommandDispatchApiController extends Controller
{
    /**
     * Elenca i comandi schedulabili via API e le rispettive opzioni accettate.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'commands' => config('schedulable_commands'),
        ]);
    }

    /**
     * Mette in coda l'esecuzione di un comando whitelisted. Risponde subito
     * (202) perché i comandi (import da API esterne, sync verso Business
     * Central) possono richiedere più tempo di quanto sia ragionevole
     * attendere in una richiesta HTTP sincrona.
     */
    public function store(Request $request, string $command): JsonResponse
    {
        $allowedCommands = config('schedulable_commands');

        if (! array_key_exists($command, $allowedCommands)) {
            return response()->json([
                'message' => "Comando '{$command}' non schedulabile via API.",
            ], 404);
        }

        $allowedOptions = $allowedCommands[$command]['options'];

        $data = $request->input('options', []);

        if (! is_array($data)) {
            return response()->json(['message' => 'Il campo "options" deve essere un oggetto.'], 422);
        }

        $unknownOptions = array_diff(array_keys($data), $allowedOptions);

        if (! empty($unknownOptions)) {
            return response()->json([
                'message' => 'Opzioni non consentite per questo comando: '.implode(', ', $unknownOptions),
                'allowed_options' => $allowedOptions,
            ], 422);
        }

        // Solo valori scalari: un array/oggetto qui verrebbe passato così com'è
        // ad Artisan::call(), che si aspetta un valore semplice per ogni opzione
        // e altrimenti fallisce con un errore di tipo poco leggibile per chi
        // chiama l'API invece di un 422 chiaro.
        $invalidOptions = array_keys(array_filter(
            $data,
            fn ($value) => $value !== null && ! is_scalar($value)
        ));

        if (! empty($invalidOptions)) {
            return response()->json([
                'message' => 'Le opzioni devono avere un valore singolo (stringa, numero o booleano): '.implode(', ', $invalidOptions),
            ], 422);
        }

        $options = [];

        foreach ($data as $name => $value) {
            $options['--'.$name] = $value;
        }

        RunArtisanCommandJob::dispatch($command, $options);

        return response()->json([
            'message' => "Comando '{$command}' accodato per l'esecuzione.",
            'command' => $command,
            'options' => $data,
        ], 202);
    }
}
