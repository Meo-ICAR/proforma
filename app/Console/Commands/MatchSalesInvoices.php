<?php

namespace App\Console\Commands;

use App\Services\SalesInvoiceMatchingService;
use Illuminate\Console\Command;

class MatchSalesInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales-invoices:match {--dry-run : Show matches without updating} {--clear : Clear existing matches before matching}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Match sales invoices with clienti using VAT number';

    /**
     * Execute the console command.
     */
    public function handle(SalesInvoiceMatchingService $matchingService)
    {
        $this->info('Starting sales invoice matching process...');

        // Show current statistics
        $stats = $matchingService->getMatchingStats();
        $this->displayStats($stats);

        // Clear matches if requested
        if ($this->option('clear')) {
            $this->warn('Clearing existing matches...');
            $cleared = $matchingService->clearMatches();
            $this->info("Cleared {$cleared} matches.");
        }

        // Perform matching
        $options = [
            'dry_run' => $this->option('dry-run'),
        ];

        $this->info('Matching sales invoices...');
        $progressBar = $this->output->createProgressBar(1);
        $progressBar->start();

        $results = $matchingService->matchSalesInvoices($options);

        $progressBar->finish();
        $this->newLine();

        // Display results
        $this->displayResults($results);

        // Show updated statistics
        if (!$options['dry_run']) {
            $this->newLine();
            $this->info('Updated statistics:');
            $newStats = $matchingService->getMatchingStats();
            $this->displayStats($newStats);
        }

        $this->info('Matching process completed!');

        return 0;
    }

    /**
     * Display matching statistics
     */
    private function displayStats(array $stats): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Invoices', number_format($stats['total_invoices'])],
                ['Invoices to Match', number_format($stats['total_to_match'])],
                ['Matched Invoices', number_format($stats['matched_invoices'])],
                ['Unmatched Invoices', number_format($stats['unmatched_invoices'])],
                ['Clienti Matches', number_format($stats['clienti_matches'])],
                ['Match Percentage', $stats['match_percentage'] . '%'],
            ]
        );
    }

    /**
     * Display matching results
     */
    private function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('Matching Results:');

        $this->table(
            ['Status', 'Count'],
            [
                ['Matched', $results['matched']],
                ['Unmatched', $results['unmatched']],
                ['Updated', $results['updated']],
                ['Errors', $results['errors']],
            ]
        );

        // Show detailed matches if not too many
        if (count($results['details']) <= 20) {
            $this->newLine();
            $this->info('Detailed Results:');

            $detailRows = [];
            foreach ($results['details'] as $detail) {
                $detailRows[] = [
                    $detail['invoice_number'] ?? $detail['invoice_id'],
                    $detail['vat_number'] ?? 'N/A',
                    $detail['matched_to'] ?? $detail['reason'] ?? 'N/A',
                    $detail['match_type'] ?? 'N/A',
                ];
            }

            $this->table(
                ['Invoice #', 'VAT Number', 'Matched To', 'Match Type'],
                $detailRows
            );
        } else {
            $this->newLine();
            $this->comment('Too many results to display. Check logs for detailed information.');
        }
    }
}
