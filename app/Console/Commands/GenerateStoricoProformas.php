<?php

namespace App\Console\Commands;

use App\Services\StoricoProformaService;
use Illuminate\Console\Command;

class GenerateStoricoProformas extends Command
{
    protected $signature = 'proformas:generate-storico
                            {--only= : Solo Istituto o Agente (default: entrambi)}
                            {--dry-run : Mostra quanti proforma creerebbe, senza scrivere}';

    protected $description = 'Crea i proforma fittizi dello storico dalle provvigioni con numero e data fattura ma senza proforma';

    public function handle(StoricoProformaService $service): int
    {
        $only = ucfirst(strtolower((string) $this->option('only')));
        if ($only !== '' && ! in_array($only, ['Istituto', 'Agente'], true)) {
            $this->error('Tipi ammessi: Istituto, Agente.');

            return self::FAILURE;
        }

        $realigned = $service->riallinea((bool) $this->option('dry-run'));
        $stats = $service->generate($only === '' ? ['Istituto', 'Agente'] : [$only], (bool) $this->option('dry-run'));

        $this->info(($stats['dry_run'] ? 'Dry-run: ' : '')."gruppi (fatture) {$stats['groups']}, proforma creati {$stats['created']}, già esistenti {$stats['reused']}, provvigioni collegate {$stats['provvigioni_linked']}");
        $this->info("Proforma storici riportati alla banca non fittizia: {$realigned}");
        foreach ($stats['skipped'] as $motivo => $n) {
            $this->warn("Saltate {$n} provvigioni: {$motivo}");
        }

        return self::SUCCESS;
    }
}
