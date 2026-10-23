<?php

namespace Tests\Feature;

use App\Models\Enasarco;
use App\Models\Firr;
use App\Models\Fornitore;
use App\Services\ReportingTables;
use Database\Seeders\ProvvigioniStatoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Il ricalcolo di vcoge, venasarcotrimestre e venasarcotot aggiorna le righe sulla chiave: non svuota la tabella.
 */
class ReportingTablesTest extends TestCase
{
    use RefreshDatabase;

    private function provvigione(int $id, array $attributes): void
    {
        DB::table('provvigioni')->insert($attributes + [
            'id' => $id, 'id_pratica' => "QT{$id}", 'stato' => null, 'importo' => 0,
        ]);
    }

    public function test_vcoge_updates_existing_months_inserts_new_ones_and_removes_only_stale_ones(): void
    {
        DB::table('vcoge')->insert([
            ['mese' => '2026-01', 'entrata' => 1, 'uscita' => 1],
            ['mese' => '2025-12', 'entrata' => 9, 'uscita' => 9],
        ]);
        $januaryId = DB::table('vcoge')->where('mese', '2026-01')->value('id');
        $this->provvigione(1, ['entrata_uscita' => 'Entrata', 'importo' => 100, 'erogated_at' => '2026-01-10 00:00:00']);
        $this->provvigione(2, ['entrata_uscita' => 'Uscita', 'importo' => 30, 'erogated_at' => '2026-02-10 00:00:00']);
        $this->provvigione(3, ['entrata_uscita' => 'Entrata', 'importo' => 40, 'status_compenso' => 'Pratica stornata', 'data_status' => '2026-01-20']);

        $result = ReportingTables::refreshVcoge();

        $this->assertSame(1, $result['deleted']);
        $this->assertSame(['2026-01', '2026-02'], DB::table('vcoge')->orderBy('mese')->pluck('mese')->all());
        $january = DB::table('vcoge')->where('mese', '2026-01')->first();
        $this->assertSame($januaryId, $january->id, 'la riga esistente va aggiornata, non ricreata');
        $this->assertEquals(100, $january->entrata);
        $this->assertEquals(40, $january->storno_entrata);
    }

    public function test_vcoge_can_be_recalculated_twice_without_duplicates(): void
    {
        $this->provvigione(1, ['entrata_uscita' => 'Entrata', 'importo' => 100, 'erogated_at' => '2026-01-10 00:00:00']);

        ReportingTables::refreshVcoge();
        ReportingTables::refreshVcoge();

        $this->assertSame(1, DB::table('vcoge')->count());
    }

    public function test_enasarco_totals_keep_ids_and_compute_the_firr_on_the_brackets(): void
    {
        $this->seed(ProvvigioniStatoSeeder::class);
        Enasarco::create(['competenza' => 2026, 'enasarco' => 'plurimandatario', 'minimo' => 0, 'massimo' => 30000, 'minimale' => 507, 'massimale' => 0, 'aliquota_soc' => 8.5, 'aliquota_agente' => 8.5]);
        Firr::create(['competenza' => 2026, 'enasarco' => 'plurimandatario', 'minimo' => 0, 'massimo' => 6200, 'aliquota' => 4]);
        Fornitore::query()->forceCreate(['name' => 'Rossi Mario', 'piva' => '12345678901', 'enasarco' => 'plurimandatario']);
        $this->provvigione(1, [
            'entrata_uscita' => 'Uscita', 'stato' => 'Pagato', 'importo' => 1000, 'data_fattura' => '2026-02-01',
            'denominazione_riferimento' => 'Rossi Mario', 'piva' => '12345678901',
        ]);

        ReportingTables::refreshEnasarcoTotali();
        $first = DB::table('venasarcotot')->first();
        ReportingTables::refreshEnasarcoTotali();

        $this->assertSame(1, DB::table('venasarcotot')->count());
        $this->assertSame($first->id, DB::table('venasarcotot')->value('id'));
        $this->assertEquals(40, DB::table('venasarcotot')->value('firr'));
    }

    public function test_enasarco_trimestre_removes_rows_that_are_no_longer_produced(): void
    {
        DB::table('venasarcotrimestre')->insert(['competenza' => 2020, 'Trimestre' => 1, 'produttore' => 'Vecchio', 'enasarco' => 'plurimandatario', 'montante' => 1, 'contributo' => 1]);

        $result = ReportingTables::refreshEnasarcoTrimestre();

        $this->assertSame(1, $result['deleted']);
        $this->assertSame(0, DB::table('venasarcotrimestre')->count());
    }
}
