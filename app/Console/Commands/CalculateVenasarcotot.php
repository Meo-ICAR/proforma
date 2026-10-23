<?php

namespace App\Console\Commands;

use App\Services\ReportingTables;
use Illuminate\Console\Command;

class CalculateVenasarcotot extends Command
{
    protected $signature = 'venasarcotot:calculate';

    protected $description = 'Ricalcola i totali ENASARCO e FIRR (tabella venasarcotot)';

    public function handle(): int
    {
        $result = ReportingTables::refreshEnasarcoTotali();

        $this->info("Calcolo ENASARCO e FIRR completato: {$result['inserted_or_updated']} righe calcolate, {$result['deleted']} eliminate.");

        return self::SUCCESS;
    }
}
