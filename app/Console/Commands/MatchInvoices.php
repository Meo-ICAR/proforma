<?php

namespace App\Console\Commands;

use App\Services\InvoiceMatchingService;
use Illuminate\Console\Command;

class MatchInvoices extends Command
{
    protected $signature = 'invoices:match
                            {--only= : Solo alcuni tipi di provvigione, separati da virgola: agente, istituto, cliente (default: tutti)}
                            {--stats-only : Mostra solo quanto resta da abbinare}';

    protected $description = 'Abbina provvigioni e proforma alle fatture: agenti alle fatture di acquisto, istituti e clienti alle fatture di vendita';

    public function handle(InvoiceMatchingService $matching): int
    {
        $tipi = array_values(array_filter(array_map('trim', explode(',', (string) ($this->option('only') ?: 'agente,istituto,cliente')))));

        if (array_diff($tipi, ['agente', 'istituto', 'cliente']) !== []) {
            $this->error('Tipi ammessi: agente, istituto, cliente.');

            return self::FAILURE;
        }

        if (! $this->option('stats-only')) {
            foreach ($matching->match($tipi) as $tipo => $stats) {
                if ($tipo === 'stati_riallineati') {
                    $this->info("Provvigioni portate a Pagato: {$stats}");

                    continue;
                }

                $abbinati = $stats['matched_proformas'] ?? $stats['matched'];
                $this->info(ucfirst($tipo).": abbinati {$abbinati}".(isset($stats['clients_created']) ? ", clienti creati {$stats['clients_created']}, aggiornati {$stats['clients_updated']}" : ''));

                foreach ($stats['errors'] as $error) {
                    $this->error("  - {$error}");
                }
            }
        }

        foreach ($matching->statistics() as $tipo => $stats) {
            $this->line(ucfirst($tipo).': '.json_encode($stats));
        }

        return self::SUCCESS;
    }
}
