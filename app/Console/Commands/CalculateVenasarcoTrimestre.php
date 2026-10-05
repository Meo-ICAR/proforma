<?php

namespace App\Console\Commands;

use App\Models\Venasarcotrimestre;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateVenasarcoTrimestre extends Command
{
    protected $signature = 'venasarco-trimestre:calculate';

    protected $description = 'Ricalcola i contributi trimestrali ENASARCO (tabella venasarcotrimestre) dalla vista vwenasarcotrimestre, poi ricalcola anche i totali ENASARCO/FIRR (venasarcotot)';

    public function handle(): int
    {
        Venasarcotrimestre::truncate();

        $columns = ['produttore', 'montante', 'competenza', 'Trimestre', 'enasarco', 'contributo'];

        DB::table('venasarcotrimestre')->insertUsing(
            $columns,
            DB::table('vwenasarcotrimestre')->select($columns)
        );

        $this->info('Calcolo trimestrale ENASARCO completato.');

        return $this->call('venasarcotot:calculate');
    }
}
