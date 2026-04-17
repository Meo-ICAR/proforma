<?php

namespace App\Services;

use App\Models\Proforma;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProformaPurchaseInvoiceMatchingService
{
    /**
     * Match proformas to purchase invoices based on amount equality
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

        // Get all purchase invoices that are not closed
        $purchaseInvoices = PurchaseInvoice::where('closed', 0)
            ->whereNotNull('amount')
            ->whereNotNull('registration_date')
            ->orderBy('registration_date')
            ->get();

        Log::info("Processing {$purchaseInvoices->count()} unclosed purchase invoices");

        foreach ($purchaseInvoices as $purchaseInvoice) {
            try {
                $this->processPurchaseInvoice($purchaseInvoice, $stats);
                $stats['processed_invoices']++;
            } catch (\Exception $e) {
                Log::error("Error processing purchase invoice {$purchaseInvoice->id}: " . $e->getMessage());
                $stats['errors'][] = "Purchase Invoice {$purchaseInvoice->id}: " . $e->getMessage();
            }
        }

        Log::info("Matching completed. Processed: {$stats['processed_invoices']}, Matched: {$stats['matched_proformas']}");

        return $stats;
    }

    /**
     * Process a single purchase invoice and try to match with proformas
     *
     * @param PurchaseInvoice $purchaseInvoice
     * @param array $stats
     */
    private function processPurchaseInvoice(PurchaseInvoice $purchaseInvoice, array &$stats): void
    {
        // Get proformas sent before or on the registration date (note: <= for purchase invoices)
        $proformas = $purchaseInvoice
            ->proformasAfterRegistration()
            ->whereNull('invoiceable_id')  // Only process unassociated proformas
            ->get();

        if ($proformas->isEmpty()) {
            Log::debug("No proformas found for purchase invoice {$purchaseInvoice->id}");
            return;
        }

        foreach ($proformas as $proforma) {
            // Calculate total amount (compenso + anticipo + contributo)
            $proformaTotal = $proforma->compenso + $proforma->anticipo + $proforma->contributo;

            // Compare with purchase invoice amount (allowing small floating point differences)
            if ($this->amountsMatch($purchaseInvoice->amount, $proformaTotal)) {
                // Associate proforma with purchase invoice
                $this->associateProformaWithInvoice($proforma, $purchaseInvoice);
                $stats['matched_proformas']++;

                Log::info("Matched proforma {$proforma->id} with purchase invoice {$purchaseInvoice->id} - Amount: {$purchaseInvoice->amount}");
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
     * Associate a proforma with a purchase invoice
     *
     * @param Proforma $proforma
     * @param PurchaseInvoice $purchaseInvoice
     */
    private function associateProformaWithInvoice(Proforma $proforma, PurchaseInvoice $purchaseInvoice): void
    {
        DB::transaction(function () use ($proforma, $purchaseInvoice) {
            $proforma->update([
                'invoiceable_type' => PurchaseInvoice::class,
                'invoiceable_id' => $purchaseInvoice->id,
            ]);
            $purchaseInvoice->update([
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

        $unmatchedInvoices = PurchaseInvoice::where('closed', 0)
            ->whereNull('invoiceable_id')
            ->whereNotNull('amount')
            ->count();

        return [
            'unmatched_proformas' => $unmatchedProformas,
            'unmatched_invoices' => $unmatchedInvoices,
        ];
    }
}
