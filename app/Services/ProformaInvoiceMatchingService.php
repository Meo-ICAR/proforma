<?php

namespace App\Services;

use App\Models\Proforma;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProformaInvoiceMatchingService
{
    /**
     * Match proformas to sales invoices based on amount equality
     *
     * @return array Statistics about the matching process
     */
    public function matchProformasToInvoices(): array
    {
        $stats = [
            'processed_invoices' => 0,
            'matched_proformas' => 0,
            'errors' => []
        ];

        // Get all sales invoices that are not closed
        $salesInvoices = SalesInvoice::where('closed', 0)
            ->whereNotNull('amount')
            ->whereNotNull('registration_date')
            ->orderBy('registration_date')
            ->get();

        Log::info("Processing {$salesInvoices->count()} unclosed sales invoices");

        foreach ($salesInvoices as $salesInvoice) {
            try {
                $this->processSalesInvoice($salesInvoice, $stats);
                $stats['processed_invoices']++;
            } catch (\Exception $e) {
                Log::error("Error processing sales invoice {$salesInvoice->id}: " . $e->getMessage());
                $stats['errors'][] = "Sales Invoice {$salesInvoice->id}: " . $e->getMessage();
            }
        }

        Log::info("Matching completed. Processed: {$stats['processed_invoices']}, Matched: {$stats['matched_proformas']}");

        return $stats;
    }

    /**
     * Process a single sales invoice and try to match with proformas
     *
     * @param SalesInvoice $salesInvoice
     * @param array $stats
     */
    private function processSalesInvoice(SalesInvoice $salesInvoice, array &$stats): void
    {
        // Get proformas sent after or on the registration date
        $proformas = $salesInvoice
            ->proformasAfterRegistration()
            ->whereNull('invoiceable_id')  // Only process unassociated proformas
            ->get();

        if ($proformas->isEmpty()) {
            Log::debug("No proformas found for sales invoice {$salesInvoice->id}");
            return;
        }

        foreach ($proformas as $proforma) {
            // Calculate total amount (compenso + anticipo + contributo)
            $proformaTotal = $proforma->compenso + $proforma->anticipo + $proforma->contributo;

            // Compare with sales invoice amount (allowing small floating point differences)
            if ($this->amountsMatch($salesInvoice->amount, $proformaTotal)) {
                // Associate the proforma with the sales invoice
                $this->associateProformaWithInvoice($proforma, $salesInvoice);
                $stats['matched_proformas']++;

                Log::info("Matched proforma {$proforma->id} with sales invoice {$salesInvoice->id} - Amount: {$salesInvoice->amount}");
            }
        }
    }

    /**
     * Check if two amounts match (allowing small floating point differences)
     *
     * @param float $amount1
     * @param float $amount2
     * @param float $tolerance
     * @return bool
     */
    private function amountsMatch(float $amount1, float $amount2, float $tolerance = 10): bool
    {
        return abs($amount1 - $amount2) <= $tolerance;
    }

    /**
     * Associate a proforma with a sales invoice
     *
     * @param Proforma $proforma
     * @param SalesInvoice $salesInvoice
     */
    private function associateProformaWithInvoice(Proforma $proforma, SalesInvoice $salesInvoice): void
    {
        DB::transaction(function () use ($proforma, $salesInvoice) {
            $proforma->update([
                'invoiceable_type' => SalesInvoice::class,
                'invoiceable_id' => $salesInvoice->id,
            ]);
            $salesInvoice->update([
                'closed' => 1,
            ]);
        });
    }

    /**
     * Get statistics about unmatched proformas
     *
     * @return array
     */
    public function getUnmatchedStatistics(): array
    {
        $unmatchedProformas = Proforma::whereNull('invoiceable_id')
            ->whereNotNull('sended_at')
            ->count();

        $unmatchedInvoices = SalesInvoice::where('closed', 0)
            ->whereNull('invoiceable_id')
            ->whereNotNull('amount')
            ->count();

        return [
            'unmatched_proformas' => $unmatchedProformas,
            'unmatched_invoices' => $unmatchedInvoices,
        ];
    }
}
