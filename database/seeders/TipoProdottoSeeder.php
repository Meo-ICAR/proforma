<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoProdottoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Usa il query builder invece del model TipoProdotto: il model imposta
     * 'tipo_prodotto' come chiave primaria (non 'id', che è la vera PK
     * auto-incrementante della tabella), e una riga del seed ha
     * tipo_prodotto = NULL — un save() via Eloquent userebbe quella colonna
     * per la clausola WHERE, con risultati inaffidabili su quella riga.
     */
    public function run(): void
    {
        $tipi = [
            ['id' => 1, 'name' => '', 'tipo_prodotto' => null, 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 2, 'name' => 'ALTRA DELEGAZIONE IMPORTO CONTENUTO', 'tipo_prodotto' => 'ALTRA DELEGAZIONE IMPORTO CONTENUTO', 'code' => null, 'is_external' => true, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 3, 'name' => 'Altro', 'tipo_prodotto' => 'Altro', 'code' => null, 'is_external' => true, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 4, 'name' => 'ASSICURAZIONE', 'tipo_prodotto' => 'ASSICURAZIONE', 'code' => null, 'is_external' => true, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 5, 'name' => 'Aziendale', 'tipo_prodotto' => 'Aziendale', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 6, 'name' => 'CASSA MUTUA', 'tipo_prodotto' => 'CASSA MUTUA', 'code' => null, 'is_external' => true, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 7, 'name' => 'Cessione', 'tipo_prodotto' => 'Cessione', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 8, 'name' => 'CHIROGRAFARIO', 'tipo_prodotto' => 'CHIROGRAFARIO', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 9, 'name' => 'Delega', 'tipo_prodotto' => 'Delega', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 10, 'name' => 'IPOTECARIO', 'tipo_prodotto' => 'IPOTECARIO', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 11, 'name' => 'LEASING', 'tipo_prodotto' => 'LEASING', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 12, 'name' => 'Microcredito', 'tipo_prodotto' => 'Microcredito', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 13, 'name' => 'Mutuo', 'tipo_prodotto' => 'Mutuo', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => false, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 14, 'name' => 'Pignoramento', 'tipo_prodotto' => 'Pignoramento', 'code' => null, 'is_external' => true, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 15, 'name' => 'Polizza', 'tipo_prodotto' => 'Polizza', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 16, 'name' => 'Prestito', 'tipo_prodotto' => 'Prestito', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 17, 'name' => 'PRESTITO AZIENDALE', 'tipo_prodotto' => 'PRESTITO AZIENDALE', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 18, 'name' => 'TFS', 'tipo_prodotto' => 'TFS', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
            ['id' => 19, 'name' => 'Utenza', 'tipo_prodotto' => 'Utenza', 'code' => null, 'is_external' => false, 'is_active' => null, 'is_oneclient' => true, 'oam' => null, 'tipo_provvigioni' => null],
        ];

        foreach ($tipi as $tipo) {
            DB::table('tipoprodotto')->updateOrInsert(['id' => $tipo['id']], $tipo);
        }
    }
}
