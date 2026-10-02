<?php

namespace App\Console\Commands;

use App\Models\Vcoge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateVcoge extends Command
{
    protected $signature = 'vcoge:calculate {--invia : Dopo il calcolo invia in contabilità la primanota del mese} {--month= : Mese da inviare (YYYY-MM), default il mese precedente la data di lancio}';

    protected $description = 'Ricalcola le provvigioni finanziarie (tabella vcoge)';

    public function handle(): int
    {
        Vcoge::truncate();

        DB::table('vcoge')->insertUsing(
            ['mese', 'entrata', 'uscita'],
            DB::table('vwcoge')->select('mese', 'entrata', 'uscita')
        );

        DB::table('vcoge')
            ->leftJoin('vwcogestorno', 'vcoge.mese', '=', 'vwcogestorno.mese')
            ->update([
                'vcoge.storno_entrata' => DB::raw('COALESCE(vwcogestorno.storno_entrata, 0)'),
                'vcoge.storno_uscita' => DB::raw('COALESCE(vwcogestorno.storno_uscita, 0)'),
            ]);

        $this->info('Calcolo provvigioni finanziarie completato.');

        if (! $this->option('invia')) {
            return self::SUCCESS;
        }

        $month = $this->option('month') ?: now()->subMonth()->format('Y-m');

        $this->info("Invio in contabilità della primanota del mese {$month}.");

        return $this->call('coge:sync-monthly', ['--month' => $month]);
    }
}
