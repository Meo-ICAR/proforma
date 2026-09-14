<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * L'azienda di riferimento del mediatore: il suo id è il default di
     * 'company_id' su clientis/clients/fornitoris (vedi manuale tecnico §4.4),
     * quindi deve esistere prima di seedare quelle tabelle in un ambiente nuovo.
     */
    public function run(): void
    {
        Company::updateOrCreate(
            ['id' => '5c044917-15b3-4471-90c9-38061fcca754'],
            [
                'name' => 'RACES FINANCE',
                'piva' => '10282211001',
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
