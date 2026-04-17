<?php

namespace App\Console\Commands;

use App\Services\ProformaInvoiceMatchingService;
use Illuminate\Console\Command;

class MatchProformasToInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proformas:match-invoices 
                            {--force : Force matching even for already associated proformas}
                            {--stats-only : Show only statistics without performing matching}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match proformas to sales invoices based on amount equality';

    /**
     * Execute the console command.
     */
    public function handle(ProformaInvoiceMatchingService $matchingService): int
    {
        $this->info('Starting proforma to invoice matching...');

        if ($this->option('stats-only')) {
            $this->showStatistics($matchingService);
            return Command::SUCCESS;
        }

        $stats = $matchingService->matchProformasToInvoices();

        $this->info('Matching completed!');
        $this->info("Processed invoices: {$stats['processed_invoices']}");
        $this->info("Matched proformas: {$stats['matched_proformas']}");

        if (!empty($stats['errors'])) {
            $this->error('Errors encountered:');
            foreach ($stats['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        $this->showStatistics($matchingService);

        return Command::SUCCESS;
    }

    /**
     * Show statistics about unmatched records
     */
    private function showStatistics(ProformaInvoiceMatchingService $matchingService): void
    {
        $stats = $matchingService->getUnmatchedStatistics();

        $this->info("\n--- Statistics ---");
        $this->info("Unmatched proformas: {$stats['unmatched_proformas']}");
        $this->info("Unmatched invoices: {$stats['unmatched_invoices']}");
    }
}
