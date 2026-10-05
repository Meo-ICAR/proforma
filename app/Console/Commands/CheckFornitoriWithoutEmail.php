<?php

namespace App\Console\Commands;

use App\Enums\Severity;
use App\Models\Fornitore;
use App\Services\CheckStatus;

class CheckFornitoriWithoutEmail extends StatusCheckCommand
{
    protected $signature = 'fornitori:check-missing-email';

    protected $description = 'Stato del controllo sui fornitori (non dipendenti) senza email, a cui non si possono inviare i proforma';

    public function checkStatus(): CheckStatus
    {
        $names = Fornitore::query()
            ->where(fn ($query) => $query->where('isdipendente', false)->orWhereNull('isdipendente'))
            ->where(fn ($query) => $query->whereNull('email')->orWhere('email', ''))
            ->pluck('name');

        return new CheckStatus(
            $names->count(),
            Severity::fromCount($names->count(), regularFrom: 1, warningFrom: 5, alertFrom: 20),
            $names->isEmpty() ? null : '- '.$names->implode("\n- "),
        );
    }
}
