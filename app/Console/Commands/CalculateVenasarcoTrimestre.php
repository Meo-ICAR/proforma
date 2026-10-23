<?php

namespace App\Console\Commands;

use App\Services\ReportingTables;
use Illuminate\Console\Command;

class CalculateVenasarcoTrimestre extends Command
{
    protected $signature = 'venasarco-trimestre:calculate';

    protected $description = 'Ricalcola i contributi trimestrali ENASARCO (tabella venasarcotrimestre), poi ricalcola anche i totali ENASARCO/FIRR (venasarcotot)';

    public function handle(): int
    {
        $result = ReportingTables::refreshEnasarcoTrimestre();

        $this->info("Calcolo trimestrale ENASARCO completato: {$result['inserted_or_updated']} righe calcolate, {$result['deleted']} eliminate.");

        return $this->call('venasarcotot:calculate');
    }
}
