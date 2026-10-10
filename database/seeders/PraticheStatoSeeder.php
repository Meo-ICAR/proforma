<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PraticheStatoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Stati possibili delle pratiche, nella tabella pratica_stati del pacchetto (prima 'pratiches_statos', FK da 'pratiches.stato_pratica',
     * vedi manuale tecnico §4.3) e i relativi flag isrejected/isworking/
     * isestingued usati per raggruppare le pratiche negli import MediaFacile.
     * Nessun model Eloquent copre 'pratiches_statos': si seeda via query
     * builder, includendo la riga con stato_pratica = '' presente nel dump.
     */
    public function run(): void
    {
        $stati = [
            ['codice' => '', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'ACCETTATO PREVENTIVO', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Approvata', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'ATTO FISSATO', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Caricata Banca', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Chiusa', 'is_rejected' => 0, 'is_working' => 0, 'is_estingued' => 0],
            ['codice' => 'DECLINATA', 'is_rejected' => 1, 'is_working' => 0, 'is_estingued' => 0],
            ['codice' => 'DELIBERATA', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'ESTINTO', 'is_rejected' => 0, 'is_working' => 0, 'is_estingued' => 1],
            ['codice' => 'FASCICOLO COMPLETO', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Fatturato', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'IN AMMORTAMENTO', 'is_rejected' => 0, 'is_working' => 0, 'is_estingued' => 1],
            ['codice' => 'IN ATTESA BENESTARE', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'In attesa documenti originali', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Inserita', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Inserito', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'INVIO IN ISTRUTTORIA', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'LIQUIDATA', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'NOTIFICA', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'ORIGINALI IN SEDE', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'PERFEZIONATA', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'PERIZIA KO', 'is_rejected' => 1, 'is_working' => 0, 'is_estingued' => 0],
            ['codice' => 'PRATICA RESPINTA', 'is_rejected' => 1, 'is_working' => 0, 'is_estingued' => 0],
            ['codice' => 'RICHIESTA EMISSIONE', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Richiesta Istruttoria', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Richiesta Polizza', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'RIENTRO BENESTARE', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'RIENTRO POLIZZA', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'RINNOVABILE', 'is_rejected' => 0, 'is_working' => 0, 'is_estingued' => 1],
            ['codice' => 'RINUNCIA CLIENTE', 'is_rejected' => 1, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'SOSPESA', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
            ['codice' => 'Sospesa Istruttoria Interna', 'is_rejected' => 0, 'is_working' => 1, 'is_estingued' => 0],
        ];

        foreach ($stati as $stato) {
            DB::table('pratica_stati')->updateOrInsert(['codice' => $stato['codice']], $stato + ['name' => $stato['codice'] !== '' ? $stato['codice'] : 'N/D']);
        }
    }
}
