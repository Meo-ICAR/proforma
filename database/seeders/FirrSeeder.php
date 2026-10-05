<?php

namespace Database\Seeders;

use App\Models\Firr;
use Illuminate\Database\Seeder;

class FirrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Scaglioni progressivi per il calcolo del FIRR (Firr::calculateContributo,
     * vedi manuale tecnico §8.3), per anno di competenza e tipologia di mandato.
     */
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'minimo' => 0.00, 'massimo' => 6200.00, 'aliquota' => 4.00, 'competenza' => 2025, 'enasarco' => 'plurimandatario'],
            ['id' => 2, 'minimo' => 6201.00, 'massimo' => 9300.00, 'aliquota' => 2.00, 'competenza' => 2025, 'enasarco' => 'plurimandatario'],
            ['id' => 3, 'minimo' => 9301.00, 'massimo' => 99999999.00, 'aliquota' => 1.00, 'competenza' => 2025, 'enasarco' => 'plurimandatario'],
            ['id' => 4, 'minimo' => 0.00, 'massimo' => 12400.00, 'aliquota' => 4.00, 'competenza' => 2025, 'enasarco' => 'monomandatario'],
            ['id' => 5, 'minimo' => 12401.00, 'massimo' => 18600.00, 'aliquota' => 2.00, 'competenza' => 2025, 'enasarco' => 'monomandatario'],
            ['id' => 6, 'minimo' => 18601.00, 'massimo' => 99999999.00, 'aliquota' => 1.00, 'competenza' => 2025, 'enasarco' => 'monomandatario'],
            ['id' => 7, 'minimo' => 0.00, 'massimo' => 6200.00, 'aliquota' => 4.00, 'competenza' => 2026, 'enasarco' => 'plurimandatario'],
            ['id' => 8, 'minimo' => 6201.00, 'massimo' => 9300.00, 'aliquota' => 2.00, 'competenza' => 2026, 'enasarco' => 'plurimandatario'],
            ['id' => 9, 'minimo' => 9301.00, 'massimo' => 99999999.00, 'aliquota' => 1.00, 'competenza' => 2026, 'enasarco' => 'plurimandatario'],
            ['id' => 10, 'minimo' => 0.00, 'massimo' => 12400.00, 'aliquota' => 4.00, 'competenza' => 2026, 'enasarco' => 'monomandatario'],
            ['id' => 11, 'minimo' => 12401.00, 'massimo' => 18600.00, 'aliquota' => 2.00, 'competenza' => 2026, 'enasarco' => 'monomandatario'],
            ['id' => 12, 'minimo' => 18601.00, 'massimo' => 99999999.00, 'aliquota' => 1.00, 'competenza' => 2026, 'enasarco' => 'monomandatario'],
        ];

        foreach ($rows as $row) {
            Firr::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
