<?php

namespace App\Console\Commands;

use App\Models\Coges as Coge;
use App\Models\Vcoge;
use App\Services\BusinessCentralService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncCogeMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coge:sync-monthly {--month= : The month to sync (format YYYY-MM). Defaults to the previous month.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizza i dati COGE mensili con Business Central';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month') ?: Carbon::now()->subMonth()->format('Y-m');

        $this->info("Inizio sincronizzazione dati COGE per il mese: {$month}");
        Log::info("Inizio invio automatico dati in contabilità per il mese: {$month}");

        $record = Vcoge::where('mese', $month)->first();

        if (!$record) {
            $this->error("Nessun dato trovato per il mese: {$month}");
            Log::warning("Nessun dato trovato nella vista vcoge per il mese: {$month}");
            return Command::FAILURE;
        }

        try {
            $coge1 = Coge::where(['fonte' => 'mediafacile', 'entrata_uscita' => 'Entrata'])->first();
            $coge2 = Coge::where(['fonte' => 'mediafacile', 'entrata_uscita' => 'Uscita'])->first();

            if (!$coge1) {
                Log::warning("Configurazione Coge per 'Entrata' (fonte mediafacile) non trovata. Uso i valori di default.");
                $this->warn("Configurazione Coge per 'Entrata' non trovata.");
            }
            if (!$coge2) {
                Log::warning("Configurazione Coge per 'Uscita' (fonte mediafacile) non trovata. Uso i valori di default.");
                $this->warn("Configurazione Coge per 'Uscita' non trovata.");
            }

            // Calculate end of month for PostingDate
            $datacoge = Carbon::createFromFormat('Y-m', $record->mese)->endOfMonth()->toDateString();
            $docnoEntrata = 'ENTRATA-' . $record->mese;
            $docnoUscita = 'USCITA-' . $record->mese;
            $entrata = (float) $record->entrata;
            $uscita = (float) $record->uscita;

            // Prepare Data
            $innerDocs = [
                [
                    'JournalTemplateName' => 'GENERALE',
                    'JournalBatchName' => 'COGEWS',
                    'LineNo' => '1',
                    'AccountNo' => str_replace('.', '', $coge1->conto_dare ?? '0128002'),
                    'PostingDate' => $datacoge,
                    'DocumentNo' =>  $docnoEntrata,
                    'Description' => $coge1->descrizione_dare ?? 'Clienti c/fatture da emettere',
                    'Amount' => -$entrata
                ],
                [
                    'JournalTemplateName' => 'GENERALE',
                    'JournalBatchName' => 'COGEWS',
                    'LineNo' => '2',
                    'AccountNo' => str_replace('.', '', $coge1->conto_avere ?? '0501001'),
                    'PostingDate' => $datacoge,
                    'DocumentNo' =>  $docnoEntrata,
                    'Description' => $coge1->descrizione_avere ?? 'Ricavi Italia',
                    'Amount' =>  $entrata
                ],
                [
                    'JournalTemplateName' => 'GENERALE',
                    'JournalBatchName' => 'COGEWS',
                    'LineNo' => '1',
                    'AccountNo' => str_replace('.', '', $coge2->conto_dare ?? '0221002'),
                    'PostingDate' => $datacoge,
                    'DocumentNo' => $docnoUscita,
                    'Description' => $coge2->descrizione_avere ?? 'Fornitori c/fatture da ricevere',
                    'Amount' => $uscita
                ],
                [
                    'JournalTemplateName' => 'GENERALE',
                    'JournalBatchName' => 'COGEWS',
                    'LineNo' => '2',
                    'AccountNo' => str_replace('.', '', $coge2->conto_avere ?? '0405019'),
                    'PostingDate' => $datacoge,
                    'DocumentNo' => $docnoUscita,
                    'Description' => $coge2->descrizione_dare ?? 'Consulenze diverse',
                    'Amount' => -$uscita
                ]
            ];

            Log::debug("Payload preparato per l'invio automatico:", $innerDocs);

            $businessCentralService = new BusinessCentralService();
            $dataResponse = $businessCentralService->inviaPrimaNota($innerDocs);

            Log::info('API Response Status: ' . $dataResponse->status());

            if ($dataResponse->successful()) {
                $this->info("Invio completato con successo per il mese: {$month}");
                Log::info("Sincronizzazione automatica completata con successo per il mese: {$month}");
                return Command::SUCCESS;
            } else {
                $errorMessage = "Errore API: " . $dataResponse->body();
                $this->error($errorMessage);
                Log::error("Errore durante l'invio automatico dati per il mese {$month}: " . $dataResponse->body());
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $errorMessage = "Eccezione imprevista: " . $e->getMessage();
            $this->error($errorMessage);
            Log::error("Eccezione imprevista durante l'invio automatico in contabilità: " . $e->getMessage(), [
                'exception' => $e,
                'mese' => $month,
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
