<?php

namespace App\Services;

use App\Models\Proforma;
use App\Models\PurchaseInvoice;
use App\Models\Fornitore;
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

        // Get all purchase invoices that are not closed and belong to a Fornitore
        $purchaseInvoices = PurchaseInvoice::where('closed', 0)
            ->where('invoiceable_type', Fornitore::class)
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
        // Query proformas that match VAT (either via vat_number or via fornitore->piva), are unassociated,
        // and were sent on or before the purchase invoice registration date.
        $proformas = Proforma::whereNull('invoiceable_id')
            ->whereNotNull('sended_at')
            ->where(function ($query) use ($purchaseInvoice) {
                $query->where('vat_number', $purchaseInvoice->vat_number)
                    ->orWhereHas('fornitore', function ($q) use ($purchaseInvoice) {
                        $q->where('piva', $purchaseInvoice->vat_number);
                    });
            })
            ->where('sended_at', '<=', $purchaseInvoice->registration_date)
            ->get();

        if ($proformas->isEmpty()) {
            Log::debug("No matching proformas found for purchase invoice {$purchaseInvoice->id} (VAT: {$purchaseInvoice->vat_number})");
            return;
        }

        foreach ($proformas as $proforma) {
            // Use the `totale` accessor which includes delta
            $proformaTotal = (float) $proforma->totale;

            // Compare with purchase invoice amount (tight tolerance for exact matching)
            if ($this->amountsMatch((float) $purchaseInvoice->amount, $proformaTotal)) {
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
    private function amountsMatch(float $amount1, float $amount2, float $tolerance = 0.01): bool
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

            $proforma->provvigioni()->where('proforma_id', $proforma->id)->update([
                'stato' => 'Pagato',
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
