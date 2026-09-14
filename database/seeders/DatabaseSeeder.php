<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            AddressTypeSeeder::class,
            ClientTypeSeeder::class,
            TipoProdottoSeeder::class,
            PraticheStatoSeeder::class,
            ProvvigioniStatoSeeder::class,
            FornitoriRoleSeeder::class,
            EnasarcoSeeder::class,
            FirrSeeder::class,
        ]);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
