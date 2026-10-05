<?php

namespace App\Console\Commands;

use App\Enums\Severity;
use App\Models\Proforma;
use App\Services\CheckStatus;

class CheckProformasUnpaid extends StatusCheckCommand
{
    protected $signature = 'proformas:check-unpaid';

    protected $description = 'Stato del controllo sui proforma inviati e non ancora pagati da oltre 30 giorni';

    private const OVERDUE_AFTER_DAYS = 30;

    public function checkStatus(): CheckStatus
    {
        $overdue = Proforma::query()
            ->where('stato', 'Inviato')
            ->where('sended_at', '<=', now()->subDays(self::OVERDUE_AFTER_DAYS))
            ->orderBy('sended_at')
            ->get(['id', 'sended_at']);

        if ($overdue->isEmpty()) {
            return new CheckStatus(0, Severity::Ok);
        }

        $oldestDays = (int) $overdue->first()->sended_at->diffInDays(now());

        // Il grado dipende da quanto è vecchio il proforma più in ritardo, non dal numero.
        $severity = match (true) {
            $oldestDays >= 60 => Severity::Alert,
            $oldestDays >= 45 => Severity::Warning,
            default => Severity::Regular,
        };

        return new CheckStatus(
            $overdue->count(),
            $severity,
            "Il proforma più vecchio è stato inviato {$oldestDays} giorni fa (soglia: ".self::OVERDUE_AFTER_DAYS.' giorni).',
        );
    }
}
