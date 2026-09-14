<?php

namespace App\Console\Commands;

use App\Models\PrimaNotaEntry;
use App\Services\BusinessCentralService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncPrimenoteEntry2Coge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coge:sync-primenote
        {--limit=200 : Numero massimo di voci di prima nota da sincronizzare in questa esecuzione}
        {--id= : Sincronizza solo la voce con questo ID, ignorando --limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invia le voci di Prima Nota non ancora sincronizzate a Business Central';

    public function handle(): int
    {
        $dareSign = -1; // config('services.business_central.primanota_dare_sign');

        if (! in_array($dareSign, [1, -1], true)) {
            $this->error(
                'BC_PRIMANOTA_DARE_SIGN non è configurato (deve essere 1 o -1). '.
                'Verificare con il commercialista/Business Central il verso contabile corretto per le righe '.
                "Dare/Avere prima di attivare l'invio (vedi config/services.php)."
            );
            Log::error('coge:sync-primenote interrotto: BC_PRIMANOTA_DARE_SIGN non configurato.');

            return Command::FAILURE;
        }

        $query = PrimaNotaEntry::whereNull('synced_at')
            ->where('is_active', true)
            ->with('config')
            ->orderBy('data');

        if ($id = $this->option('id')) {
            $entries = $query->where('id', $id)->get();
        } else {
            $entries = $query->limit((int) $this->option('limit'))->get();
        }

        if ($entries->isEmpty()) {
            $this->info('Nessuna voce di prima nota da sincronizzare.');

            return Command::SUCCESS;
        }

        $this->info("Sincronizzazione di {$entries->count()} voci di prima nota verso Business Central...");
        Log::info("Inizio sincronizzazione di {$entries->count()} voci di prima nota verso Business Central.");

        $innerDocs = [];

        foreach ($entries as $entry) {
            $documentNo = 'PRIMANOTA-'.$entry->id;
            $description = $entry->config?->event_label ?? 'Prima Nota';
            $amount = (float) $entry->importo;

            $innerDocs[] = [
                'JournalTemplateName' => 'GENERALE',
                'JournalBatchName' => 'COGEWS',
                'LineNo' => '1',
                'AccountNo' => str_replace('.', '', $entry->conto_dare),
                'PostingDate' => $entry->data->toDateString(),
                'DocumentNo' => $documentNo,
                'Description' => $description,
                'Amount' => $dareSign * $amount,
            ];

            $innerDocs[] = [
                'JournalTemplateName' => 'GENERALE',
                'JournalBatchName' => 'COGEWS',
                'LineNo' => '2',
                'AccountNo' => str_replace('.', '', $entry->conto_avere),
                'PostingDate' => $entry->data->toDateString(),
                'DocumentNo' => $documentNo,
                'Description' => $description,
                'Amount' => -$dareSign * $amount,
            ];
        }

        Log::debug('Payload preparato per la sincronizzazione prima nota:', $innerDocs);

        try {
            $businessCentralService = new BusinessCentralService;
            $dataResponse = $businessCentralService->inviaPrimaNota($innerDocs);

            if ($dataResponse->successful()) {
                $entries->each->update(['synced_at' => Carbon::now(), 'sync_error' => null]);

                $this->info("Sincronizzazione completata: {$entries->count()} voci inviate.");
                Log::info("Sincronizzazione prima nota completata con successo: {$entries->count()} voci inviate.");

                return Command::SUCCESS;
            }

            $errorMessage = 'Errore API: '.$dataResponse->body();

            $entries->each->update(['sync_error' => $errorMessage]);

            $this->error($errorMessage);
            Log::error("Errore durante la sincronizzazione prima nota: {$errorMessage}");

            return Command::FAILURE;
        } catch (\Throwable $e) {
            $errorMessage = 'Eccezione imprevista: '.$e->getMessage();

            $entries->each->update(['sync_error' => $errorMessage]);

            $this->error($errorMessage);
            Log::error('Eccezione imprevista durante la sincronizzazione prima nota: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return Command::FAILURE;
        }
    }
}
