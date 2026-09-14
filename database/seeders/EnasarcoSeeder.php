<?php

namespace Database\Seeders;

use App\Models\Enasarco;
use Illuminate\Database\Seeder;

class EnasarcoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Aliquote/massimali ENASARCO per anno di competenza e tipologia di
     * mandato, usati dal calcolo dei contributi trimestrali/annuali
     * (vwenasarco*, vedi manuale tecnico §8).
     */
    public function run(): void
    {
        $rows = [
            ['id' => 1, 'competenza' => 2025, 'enasarco' => 'monomandatario', 'minimo' => 0.00, 'massimo' => 45085.00, 'minimale' => 1011.00, 'massimale' => 0.00, 'aliquota_soc' => 8.50, 'aliquota_agente' => 8.50],
            ['id' => 3, 'competenza' => 2025, 'enasarco' => 'societa', 'minimo' => 0.00, 'massimo' => 45085.00, 'minimale' => 1011.00, 'massimale' => 0.00, 'aliquota_soc' => 0.00, 'aliquota_agente' => 0.00],
            ['id' => 4, 'competenza' => 2025, 'enasarco' => 'plurimandatario', 'minimo' => 0.00, 'massimo' => 30057.00, 'minimale' => 507.00, 'massimale' => 0.00, 'aliquota_soc' => 8.50, 'aliquota_agente' => 8.50],
            ['id' => 5, 'competenza' => 2026, 'enasarco' => 'monomandatario', 'minimo' => 0.00, 'massimo' => 45717.00, 'minimale' => 1011.00, 'massimale' => 0.00, 'aliquota_soc' => 8.50, 'aliquota_agente' => 8.50],
            ['id' => 6, 'competenza' => 2026, 'enasarco' => 'societa', 'minimo' => 0.00, 'massimo' => 13000000.00, 'minimale' => 1011.00, 'massimale' => 0.00, 'aliquota_soc' => 0.00, 'aliquota_agente' => 0.00],
            ['id' => 7, 'competenza' => 2026, 'enasarco' => 'plurimandatario', 'minimo' => 0.00, 'massimo' => 30478.00, 'minimale' => 507.00, 'massimale' => 0.00, 'aliquota_soc' => 8.50, 'aliquota_agente' => 8.50],
        ];

        foreach ($rows as $row) {
            Enasarco::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}
