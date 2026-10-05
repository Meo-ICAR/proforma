<?php

namespace App\Console\Commands;

use App\Models\Firr;
use App\Models\Venasarcotot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateVenasarcotot extends Command
{
    protected $signature = 'venasarcotot:calculate';

    protected $description = 'Ricalcola i totali ENASARCO e FIRR (tabella venasarcotot) dalla vista vwenasarcotot';

    public function handle(): int
    {
        Venasarcotot::truncate();

        DB::table('venasarcotot')->insertUsing(
            ['produttore', 'montante', 'contributo', 'X', 'imposta', 'firr', 'competenza', 'enasarco'],
            DB::table('vwenasarcotot')
        );

        foreach (Venasarcotot::get() as $record) {
            $record->update([
                'firr' => Firr::calculateContributo($record->montante, $record->enasarco, $record->competenza),
            ]);
        }

        $this->info('Calcolo ENASARCO e FIRR completato.');

        return self::SUCCESS;
    }
}
