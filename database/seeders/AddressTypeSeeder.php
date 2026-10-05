<?php

namespace Database\Seeders;

use App\Models\AddressType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => 1, 'name' => 'Residenza'],
            ['id' => 2, 'name' => 'Domicilio'],
            ['id' => 3, 'name' => 'Domicilio Legale'],
            ['id' => 4, 'name' => 'Domicilio Operativo'],
            ['id' => 5, 'name' => 'Sede Legale'],
            ['id' => 6, 'name' => 'Sede Operativa'],
        ];

        // Usiamo updateOrCreate per evitare duplicati se lanciato più volte
        foreach ($types as $type) {
            AddressType::updateOrCreate(['id' => $type['id']], $type);
        }
    }
}
