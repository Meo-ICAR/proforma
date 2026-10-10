<?php

namespace Tests\Feature;

use App\Models\Pratica;
use App\Models\Provvigione;
use Database\Seeders\ProvvigioniStatoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Unico\Core\Models\PraticaStato;
use Unico\Core\Models\TipoProdotto;

/**
 * Proforma sulle tabelle del pacchetto unico-core: pratica identificata dal codice, stato e tipo prodotto come testo
 * (compatibilità con import e form), viste di reporting.
 */
class CoreIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** `upload_at` non ha un valore predefinito: lo imposta l'import (ImportPraticheFromApi). */
    private function pratica(array $attributes): Pratica
    {
        return Pratica::create($attributes + ['upload_at' => now()]);
    }

    public function test_a_pratica_is_identified_by_its_code_and_has_an_integer_id(): void
    {
        $pratica = $this->pratica(['codice_pratica' => 'QT06585', 'nome_cliente' => 'Mario']);

        $this->assertIsInt($pratica->id);
        $this->assertSame($pratica->id, Pratica::where('codice_pratica', 'QT06585')->value('id'));
    }

    public function test_stato_and_tipo_prodotto_are_still_written_and_read_as_text(): void
    {
        $pratica = $this->pratica([
            'codice_pratica' => 'QT00001',
            'stato_pratica' => 'DECLINATA',
            'tipo_prodotto' => 'Mutuo',
        ]);

        $fresh = Pratica::find($pratica->id);
        $this->assertSame('DECLINATA', $fresh->stato_pratica);
        $this->assertSame('Mutuo', $fresh->tipo_prodotto);
        $this->assertSame('DECLINATA', PraticaStato::find($fresh->pratica_stato_id)->codice);
        $this->assertSame('Mutuo', TipoProdotto::find($fresh->tipoprodotto_id)->tipo_prodotto);
    }

    public function test_the_same_stato_is_reused_and_blank_clears_it(): void
    {
        $this->pratica(['codice_pratica' => 'QT00002', 'stato_pratica' => 'Inserita']);
        $second = $this->pratica(['codice_pratica' => 'QT00003', 'stato_pratica' => 'Inserita']);

        $this->assertSame(1, PraticaStato::where('codice', 'Inserita')->count());

        $second->update(['stato_pratica' => '']);

        $this->assertNull($second->fresh()->pratica_stato_id);
        $this->assertNull($second->fresh()->stato_pratica);
    }

    public function test_provvigioni_are_linked_to_the_pratica_through_pratica_id(): void
    {
        $this->seed(ProvvigioniStatoSeeder::class);
        $pratica = $this->pratica(['codice_pratica' => 'QT00010']);
        DB::table('provvigioni')->insert(['id' => 1, 'id_pratica' => 'QT00010', 'pratica_id' => $pratica->id, 'stato' => 'Proforma', 'importo' => 100]);
        $id = 1;

        $this->assertSame($pratica->id, Provvigione::find($id)->pratica->id);
        $this->assertSame(1, $pratica->provvigioni()->count());
    }

    public function test_the_reporting_views_used_by_the_code_exist_and_run(): void
    {
        $used = ['vwcoge', 'vwcogestorno', 'vwenasarco', 'vwenasarcotot', 'vwenasarcotrimestre', 'vwproformaagente', 'vwproformaistituto'];
        $existing = collect(DB::select('select table_name as name from information_schema.views where table_schema = database()'))->pluck('name')->all();

        foreach ($used as $view) {
            $this->assertContains($view, $existing, "vista {$view} mancante");
        }

        // Quelle usate dal codice devono girare; delle altre (report nel solo database) basta l'esistenza: alcune non sono
        // valide con ONLY_FULL_GROUP_BY, come già nel database precedente.
        foreach ($used as $view) {
            DB::select("select * from `{$view}` limit 1");
        }

        $this->assertGreaterThanOrEqual(20, count($existing));
    }

    public function test_the_pratica_views_join_on_the_foreign_key(): void
    {
        $this->seed(ProvvigioniStatoSeeder::class);
        $pratica = $this->pratica(['codice_pratica' => 'QT00020', 'nome_cliente' => 'Rossi', 'cognome_cliente' => 'Mario', 'tipo_prodotto' => 'Cessione']);
        foreach ([1, 2] as $n) {
            DB::table('provvigioni')->insert([
                'id' => $n, 'id_pratica' => 'QT00020', 'pratica_id' => $pratica->id, 'stato' => 'Proforma', 'importo' => 50, 'tipo' => 'T',
                'denominazione_riferimento' => 'X', 'descrizione' => 'D', 'data_fattura' => '2026-01-01',
            ]);
        }

        $rows = DB::select('select * from vwprovvdoppie');

        $this->assertCount(1, $rows);
        $this->assertSame('QT00020', $rows[0]->id_pratica);
        $this->assertSame('Cessione', $rows[0]->tipo_prodotto);
    }

    public function test_a_provvigione_cannot_point_to_a_missing_pratica(): void
    {
        $this->seed(ProvvigioniStatoSeeder::class);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('provvigioni')->insert(['id' => 1, 'id_pratica' => 'QT99999', 'pratica_id' => 999999, 'stato' => 'Proforma', 'importo' => 1]);
    }

    public function test_the_import_update_joins_provvigioni_to_pratiche_through_pratica_id(): void
    {
        $this->seed(ProvvigioniStatoSeeder::class);
        $pratica = $this->pratica(['codice_pratica' => 'QT00030', 'erogated_at' => '2026-03-01 00:00:00']);
        DB::table('provvigioni')->insert(['id' => 1, 'id_pratica' => 'QT00030', 'pratica_id' => $pratica->id, 'stato' => 'Proforma', 'importo' => 80, 'erogated_at' => '2026-02-01 00:00:00']);

        // stessa istruzione di ImportProvvigioniFromApi
        $updated = DB::update('UPDATE provvigioni p inner join pratiches pr on pr.id = p.pratica_id SET p.erogated_at = pr.erogated_at, p.importo_erogato= p.importo WHERE p.erogated_at <>pr.erogated_at and pr.erogated_at is not null');

        $this->assertSame(1, $updated);
        $this->assertSame('2026-03-01 00:00:00', (string) DB::table('provvigioni')->value('erogated_at'));
    }
}
