<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * L'azienda di riferimento del mediatore. Nel pacchetto unico-core 'company_id' non ha più un valore predefinito
     * (era il tenant fisso 5c044917-… su clientis/clients/fornitoris) e l'id è intero: l'azienda si riconosce dalla partita IVA.
     */
    public function run(): void
    {
        Company::updateOrCreate(
            ['vat_number' => '10282211001'],
            [
                'name' => 'RACES FINANCE',
                'email' => 'amministrazione@races.it',
                'email_cc' => 'amministrazione@races.it',
                'email_bcc' => 'amministrazione@races.it',
                'emailsubject' => 'Proforma compensi provvigionali',
                'compenso_descrizione' => "Per compensi provvigionali maturati.\r\nIMPORTANTE: Vogliate inserire nella causale della fattura l'oggetto di questa email",
                'db_port' => '3306',
            ]
        );
    }
}
