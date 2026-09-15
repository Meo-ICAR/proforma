<?php

namespace Database\Seeders;

use App\Models\PrimaNotaConfig;
use App\Models\Proforma;
use Illuminate\Database\Seeder;

class PrimaNotaConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Regole di prima nota per la gestione degli anticipi provvigionali ai
     * collaboratori: erogazione, recupero mensile e recupero costi sostenuti
     * per loro conto. Tutte e tre operano su Proforma::anticipo (segno
     * discriminato da is_positive) e su Proforma::sended_at come data evento.
     *
     * NOTA: le regole "Recupero anticipo" e "Recupero costi" condividono lo
     * stesso model_type/value_field/is_positive (Proforma, anticipo, > 0):
     * genereranno quindi una voce di prima nota ciascuna per ogni proforma
     * con anticipo positivo inviato. Se questo non è l'intento, disattivare
     * (is_active=false) quella delle due non in uso prima di lanciare
     * primanota:generate.
     */
    public function run(): void
    {
        $rules = [
            [
                'name' => 'Erogazione anticipo a collaboratore',
                'model_type' => Proforma::class,
                'value_field' => 'anticipo',
                'is_positive' => false,
                'date_field' => 'sended_at',
                'conto_dare' => '01.22.020',
                'conto_dare_description' => 'Fornitori c/anticipi',
                'conto_avere' => '02.21.002',
                'conto_avere_description' => 'Forn. c/fatt. da ricevere',
                'is_active' => true,
            ],
            [
                'name' => 'Recupero anticipo da collaboratore',
                'model_type' => Proforma::class,
                'value_field' => 'anticipo',
                'is_positive' => true,
                'date_field' => 'sended_at',
                'conto_dare' => '02.21.002',
                'conto_dare_description' => 'Forn. c/fatt. da ricevere',
                'conto_avere' => '01.22.020',
                'conto_avere_description' => 'Fornitori c/anticipi',
                'is_active' => true,
            ],
            [
                'name' => 'Recupero costi sostenuti per conto dei collaboratori',
                'model_type' => Proforma::class,
                'value_field' => 'spese',
                'is_positive' => true,
                'date_field' => 'sended_at',
                'conto_dare' => '02.21.002',
                'conto_dare_description' => 'Forn. c/fatt. da ricevere',
                'conto_avere' => '05.01.006',
                'conto_avere_description' => 'Recupero costi da collaboratori',
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            PrimaNotaConfig::updateOrCreate(['name' => $rule['name']], $rule);
        }
    }
}
