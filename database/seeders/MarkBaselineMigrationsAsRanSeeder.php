<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * NON fa parte di DatabaseSeeder::run() e non va lanciato su un ambiente
 * dove le tabelle legacy non esistono ancora (creerebbe righe fantasma
 * nella tabella migrations senza le tabelle corrispondenti).
 *
 * Uso previsto: un ambiente — tipicamente produzione — dove le tabelle
 * coperte da queste migration esistono già con i dati reali (vedi manuale
 * tecnico §3/§16). Lanciato PRIMA del primo `php artisan migrate` su
 * quell'ambiente dopo il deploy di queste migration, evita che Laravel
 * tenti di rieseguire le CREATE TABLE/VIEW su tabelle già esistenti
 * (fallirebbe con "table already exists").
 *
 * Per ogni migration, verifica che la sua tabella/vista principale esista
 * già prima di marcarla come eseguita: se manca, la migration viene
 * saltata (loggata) e resterà "Pending", cioè verrà eseguita normalmente
 * da `php artisan migrate` — comportamento corretto sia per un ambiente
 * che parte da zero, sia per una tabella genuinamente nuova (es. se in
 * futuro si aggiunge una migration per una tabella che su questo target
 * non esiste ancora, va tolta da questo elenco o gestita a parte).
 *
 *     php artisan db:seed --class=MarkBaselineMigrationsAsRanSeeder --force
 */
class MarkBaselineMigrationsAsRanSeeder extends Seeder
{
    /**
     * Migration da marcare come eseguite, con la tabella/vista che deve
     * già esistere perché sia sicuro farlo. Include sia le migration di
     * baseline (schema legacy) sia prima_nota_configs/prima_nota_entries:
     * lo schema di produzione fornito le comprende già entrambe.
     *
     * @var array<string, string>
     */
    private const BASELINE_MIGRATIONS = [
        '2026_09_14_090000_create_reference_and_lookup_tables' => 'enasarcos',
        '2026_09_14_090100_create_framework_and_company_tables' => 'companies',
        '2026_09_14_090200_create_clientis_and_fornitoris_tables' => 'fornitoris',
        '2026_09_14_090300_create_clients_tables' => 'clients',
        '2026_09_14_090400_create_pratiche_and_provvigioni_tables' => 'provvigioni',
        '2026_09_14_090500_create_fatturazione_tables' => 'invoices',
        '2026_09_14_090600_create_reporting_views' => 'vwenasarco',
        '2026_09_15_021007_create_prima_nota_configs_table' => 'prima_nota_configs',
        '2026_09_15_021102_create_prima_nota_entries_table' => 'prima_nota_entries',
    ];

    public function run(): void
    {
        $connection = DB::connection()->getName();
        $database = DB::connection()->getDatabaseName();

        $alreadyRecorded = DB::table('migrations')->pluck('migration')->all();

        $nextBatch = (int) DB::table('migrations')->max('batch') + 1;

        foreach (self::BASELINE_MIGRATIONS as $migration => $checkTable) {
            if (in_array($migration, $alreadyRecorded, true)) {
                $this->command?->line("Già presente in migrations, saltata: {$migration}");

                continue;
            }

            $exists = DB::connection($connection)
                ->table('information_schema.tables')
                ->where('table_schema', $database)
                ->where('table_name', $checkTable)
                ->exists();

            if (! $exists) {
                $this->command?->warn(
                    "Tabella/vista '{$checkTable}' non trovata: {$migration} NON marcata come eseguita. ".
                    'Verrà eseguita normalmente da `php artisan migrate` (comportamento corretto se questo '.
                    'ambiente non ha ancora lo schema legacy).'
                );

                continue;
            }

            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $nextBatch,
            ]);

            $this->command?->info("Marcata come già eseguita: {$migration}");
        }
    }
}
