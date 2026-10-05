<?php

namespace App\Console\Commands;

use App\Enums\Severity;
use App\Models\SalesInvoice;
use App\Services\CheckStatus;
use Illuminate\Support\Carbon;

class CheckStaleSalesInvoices extends StatusCheckCommand
{
    protected $signature = 'sales-invoices:check-stale';

    protected $description = "Stato del controllo sull'età dell'ultima fattura di vendita caricata (il valore è in giorni)";

    public function checkStatus(): CheckStatus
    {
        $latest = SalesInvoice::query()->latest('created_at')->value('created_at');

        if (! $latest) {
            return new CheckStatus('nessuna fattura', Severity::Alert);
        }

        $days = (int) Carbon::parse($latest)->diffInDays(now());

        return new CheckStatus(
            $days,
            Severity::fromCount($days, regularFrom: 15, warningFrom: 30, alertFrom: 45),
            "L'ultima fattura di vendita è stata caricata {$days} giorni fa.",
        );
    }
}
