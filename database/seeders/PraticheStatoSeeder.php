<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PraticheStatoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Stati possibili di 'pratiches.stato_pratica' (FK verso questa tabella,
     * vedi manuale tecnico §4.3) e i relativi flag isrejected/isworking/
     * isestingued usati per raggruppare le pratiche negli import MediaFacile.
     * Nessun model Eloquent copre 'pratiches_statos': si seeda via query
     * builder, includendo la riga con stato_pratica = '' presente nel dump.
     */
    public function run(): void
    {
        $stati = [
            ['stato_pratica' => '', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'ACCETTATO PREVENTIVO', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Approvata', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'ATTO FISSATO', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Caricata Banca', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Chiusa', 'isrejected' => 0, 'isworking' => 0, 'isestingued' => 0],
            ['stato_pratica' => 'DECLINATA', 'isrejected' => 1, 'isworking' => 0, 'isestingued' => 0],
            ['stato_pratica' => 'DELIBERATA', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'ESTINTO', 'isrejected' => 0, 'isworking' => 0, 'isestingued' => 1],
            ['stato_pratica' => 'FASCICOLO COMPLETO', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Fatturato', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'IN AMMORTAMENTO', 'isrejected' => 0, 'isworking' => 0, 'isestingued' => 1],
            ['stato_pratica' => 'IN ATTESA BENESTARE', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'In attesa documenti originali', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Inserita', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Inserito', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'INVIO IN ISTRUTTORIA', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'LIQUIDATA', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'NOTIFICA', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'ORIGINALI IN SEDE', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'PERFEZIONATA', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'PERIZIA KO', 'isrejected' => 1, 'isworking' => 0, 'isestingued' => 0],
            ['stato_pratica' => 'PRATICA RESPINTA', 'isrejected' => 1, 'isworking' => 0, 'isestingued' => 0],
            ['stato_pratica' => 'RICHIESTA EMISSIONE', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Richiesta Istruttoria', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Richiesta Polizza', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'RIENTRO BENESTARE', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'RIENTRO POLIZZA', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'RINNOVABILE', 'isrejected' => 0, 'isworking' => 0, 'isestingued' => 1],
            ['stato_pratica' => 'RINUNCIA CLIENTE', 'isrejected' => 1, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'SOSPESA', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
            ['stato_pratica' => 'Sospesa Istruttoria Interna', 'isrejected' => 0, 'isworking' => 1, 'isestingued' => 0],
        ];

        foreach ($stati as $stato) {
            DB::table('pratiches_statos')->updateOrInsert(['stato_pratica' => $stato['stato_pratica']], $stato);
        }
    }
}
