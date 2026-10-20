<?php

namespace App\Console\Commands;

use App\Services\ProvvigioneInvoiceMatchingService;
use Illuminate\Console\Command;

class MatchProvvigioniToSalesInvoices extends Command
{
    protected $signature = 'provvigioni:match-sales-invoices {--stats-only : Mostra solo le statistiche, senza abbinare}';

    protected $description = 'Abbina le provvigioni di tipo Cliente alle fatture di vendita (codice fiscale della pratica, nome e importo), senza passare per il proforma';

    public function handle(ProvvigioneInvoiceMatchingService $matching): int
    {
        if (! $this->option('stats-only')) {
            $stats = $matching->matchClienteProvvigioniToSalesInvoices();

            $this->info("Provvigioni elaborate: {$stats['processed']}");
            $this->info("Abbinate a una fattura: {$stats['matched']}");
            $this->info("Clienti creati: {$stats['clients_created']}, aggiornati con il codice fiscale: {$stats['clients_updated']}");

            foreach ($stats['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        $unmatched = $matching->getUnmatchedStatistics();
        $this->info("Provvigioni Cliente senza fattura: {$unmatched['unmatched_provvigioni']}");
        $this->info("Fatture di vendita a persone senza abbinamento: {$unmatched['unmatched_invoices']}");

        return self::SUCCESS;
    }
}
