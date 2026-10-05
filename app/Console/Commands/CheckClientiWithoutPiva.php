<?php

namespace App\Console\Commands;

use App\Enums\Severity;
use App\Models\Clienti;
use App\Services\CheckStatus;

class CheckClientiWithoutPiva extends StatusCheckCommand
{
    protected $signature = 'clienti:check-missing-piva';

    protected $description = 'Stato del controllo sugli istituti attivi (non fittizi) senza partita IVA';

    public function checkStatus(): CheckStatus
    {
        $names = Clienti::activeWithoutPiva()->pluck('name');

        return new CheckStatus(
            $names->count(),
            Severity::fromCount($names->count(), regularFrom: 1, warningFrom: 3, alertFrom: 10),
            $names->isEmpty() ? null : '- '.$names->implode("\n- "),
        );
    }
}
