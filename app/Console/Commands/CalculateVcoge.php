<?php

namespace App\Console\Commands;

use App\Services\ReportingTables;
use Illuminate\Console\Command;

class CalculateVcoge extends Command
{
    protected $signature = 'vcoge:calculate {--invia : Dopo il calcolo invia in contabilità la primanota del mese} {--month= : Mese da inviare (YYYY-MM), default il mese precedente la data di lancio}';

    protected $description = 'Ricalcola le provvigioni finanziarie (tabella vcoge)';

    public function handle(): int
    {
        $result = ReportingTables::refreshVcoge();

        $this->info("Calcolo provvigioni finanziarie completato: {$result['inserted_or_updated']} mesi calcolati, {$result['deleted']} eliminati.");

        if (! $this->option('invia')) {
            return self::SUCCESS;
        }

        $month = $this->option('month') ?: now()->subMonth()->format('Y-m');

        $this->info("Invio in contabilità della primanota del mese {$month}.");

        return $this->call('coge:sync-monthly', ['--month' => $month]);
    }
}
