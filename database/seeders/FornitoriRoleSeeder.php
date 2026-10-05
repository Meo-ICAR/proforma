<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FornitoriRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Nessun model Eloquent copre la tabella 'fornitoriroles' (vedi
     * Fornitore::fornitorirole_id in app/Models/Fornitore.php): si seeda
     * direttamente via query builder invece di introdurne uno per un
     * semplice upsert.
     */
    public function run(): void
    {
        $ruoli = [
            ['id' => 1, 'name' => 'Segnalatore / Front-End', 'code' => 'SEGNALATORE', 'level' => 1, 'description' => 'Fornisce solo il contatto del cliente, provvigione minima o fissa'],
            ['id' => 2, 'name' => 'Agente Junior', 'code' => 'AG_JUNIOR', 'level' => 2, 'description' => 'Collaboratore operativo junior'],
            ['id' => 3, 'name' => 'Agente Senior', 'code' => 'AG_SENIOR', 'level' => 3, 'description' => 'Agente autonomo con portafoglio e provvigioni piene'],
            ['id' => 4, 'name' => 'Area Manager / Supervisor', 'code' => 'MANAGER', 'level' => 4, 'description' => 'Responsabile di area con diritto a provvigioni di sormonto (overriding)'],
            ['id' => 5, 'name' => 'Backoffice / Amministrativo', 'code' => 'BACKOFFICE', 'level' => 5, 'description' => 'Personale interno di segreteria e caricamento pratiche'],
        ];

        foreach ($ruoli as $ruolo) {
            DB::table('fornitoriroles')->updateOrInsert(['id' => $ruolo['id']], $ruolo);
        }
    }
}
