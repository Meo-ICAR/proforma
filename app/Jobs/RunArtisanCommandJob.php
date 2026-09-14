<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Esegue in background un comando Artisan tra quelli elencati nella whitelist
 * config('schedulable_commands'), lanciato tramite il bridge API esposto a un
 * sistema esterno di scheduling (vedi App\Http\Controllers\Api\CommandDispatchApiController).
 */
class RunArtisanCommandJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 3;

    /**
     * Backoff crescente fra i tentativi: un fallimento transitorio (es. l'API
     * di MediaFacile momentaneamente irraggiungibile) ha così il tempo di
     * risolversi prima del retry, invece di ripresentarsi immediatamente.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $command,
        public array $options = [],
    ) {}

    public function handle(): void
    {
        // Evita esecuzioni sovrapposte dello stesso comando (indipendentemente
        // dalle opzioni), sullo stesso principio di Schedule::withoutOverlapping():
        // se una chiamata precedente allo stesso comando è ancora in corso, questa
        // viene saltata invece di essere trattata come un fallimento da ritentare.
        $lock = Cache::lock("schedulable-command:{$this->command}", $this->timeout);

        if (! $lock->get()) {
            Log::info("Comando '{$this->command}' saltato: un'esecuzione precedente è ancora in corso.", [
                'options' => $this->options,
            ]);

            return;
        }

        try {
            $exitCode = Artisan::call($this->command, $this->options);
            $output = Artisan::output();

            if ($exitCode === 0) {
                Log::info("Comando '{$this->command}' eseguito con successo via API.", [
                    'options' => $this->options,
                    'output' => $output,
                ]);

                return;
            }

            Log::error("Comando '{$this->command}' terminato con errore via API (exit code {$exitCode}), tentativo {$this->attempts()}/{$this->tries}.", [
                'options' => $this->options,
                'output' => $output,
            ]);

            // Un exit code diverso da zero deve far scattare il retry di Laravel:
            // senza un'eccezione il job veniva marcato "completato" anche se il
            // comando sottostante falliva, e non veniva mai ritentato.
            throw new RuntimeException("Comando '{$this->command}' terminato con exit code {$exitCode}.");
        } finally {
            $lock->release();
        }
    }

    /**
     * Loggato una volta esauriti tutti i tentativi (dopo l'ultimo backoff).
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Comando '{$this->command}' non riuscito dopo {$this->tries} tentativi via API.", [
            'options' => $this->options,
            'error' => $exception->getMessage(),
        ]);
    }
}
