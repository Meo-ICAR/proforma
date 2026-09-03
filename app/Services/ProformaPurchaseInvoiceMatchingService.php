<?php

namespace App\Services;

use App\Models\Fornitore;
use App\Models\Proforma;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProformaPurchaseInvoiceMatchingService
{
    /**
     * Match proformas to purchase invoices.
     *
     * Legame:
     *   PurchaseInvoice.vat_number  =  Fornitore.piva  =  Proforma.fornitori_id (FK → fornitoris.id)
     *
     * @return array{processed_invoices: int, matched_proformas: int, errors: string[]}
     */
    public function matchProformasToInvoices(): array
    {
        $stats = [
            'processed_invoices' => 0,
            'matched_proformas'  => 0,
            'errors'             => [],
        ];

        // Carica solo le PurchaseInvoice non chiuse e con vat_number valorizzato.
        $purchaseInvoices = PurchaseInvoice::where('closed', false)
            ->whereNotNull('vat_number')
            ->whereNotNull('amount')
            ->whereNotNull('registration_date')
            ->orderBy('registration_date')
            ->get();

        Log::info("[PurchaseMatching] Fatture da elaborare: {$purchaseInvoices->count()}");

        foreach ($purchaseInvoices as $purchaseInvoice) {
            try {
                $this->processPurchaseInvoice($purchaseInvoice, $stats);
                $stats['processed_invoices']++;
            } catch (\Throwable $e) {
                Log::error("[PurchaseMatching] Errore su fattura {$purchaseInvoice->id}: " . $e->getMessage());
                $stats['errors'][] = "PurchaseInvoice {$purchaseInvoice->id}: " . $e->getMessage();
            }
        }

        Log::info("[PurchaseMatching] Completato. Elaborate: {$stats['processed_invoices']}, Abbinate: {$stats['matched_proformas']}");

        return $stats;
    }

    /**
     * Tenta di abbinare una PurchaseInvoice a una Proforma.
     *
     * Strategia A — tramite fornitori_id:
     *   Trova il Fornitore (inclusi soft-deleted) per P.IVA, poi cerca le sue proforma.
     *   Se nessuna combacia per importo → attiva la strategia B.
     *
     * Strategia B — fallback su vat_number denormalizzato:
     *   Cerca le proforma direttamente per proforma.vat_number.
     *   Copre i casi in cui la proforma ha un fornitori_id disallineato o il fornitore
     *   non esiste più, ma il campo vat_number è corretto.
     */
    private function processPurchaseInvoice(PurchaseInvoice $purchaseInvoice, array &$stats): void
    {
        // Strategia A: tramite Fornitore (inclusi soft-deleted)
        $fornitore = Fornitore::withTrashed()
            ->where('piva', $purchaseInvoice->vat_number)
            ->first();

        if ($fornitore) {
            $proformas = Proforma::where('fornitori_id', $fornitore->id)
                ->whereNull('invoiceable_id')
                ->whereNotNull('sended_at')
                ->where('sended_at', '<=', $purchaseInvoice->registration_date)
                ->get();

            if ($this->tryMatchByAmount($proformas, $purchaseInvoice, $stats)) {
                return;
            }
        }

        // Strategia B: fallback su vat_number denormalizzato
        Log::debug("[PurchaseMatching] Strategia B (vat_number) per fattura {$purchaseInvoice->id} (P.IVA: {$purchaseInvoice->vat_number})");

        $proformasByVat = Proforma::where('vat_number', $purchaseInvoice->vat_number)
            ->whereNull('invoiceable_id')
            ->whereNotNull('sended_at')
            ->where('sended_at', '<=', $purchaseInvoice->registration_date)
            ->get();

        if ($proformasByVat->isEmpty()) {
            Log::debug("[PurchaseMatching] Nessuna proforma per P.IVA '{$purchaseInvoice->vat_number}' (fattura {$purchaseInvoice->id})");
            return;
        }

        $this->tryMatchByAmount($proformasByVat, $purchaseInvoice, $stats);
    }

    /**
     * Scorre le proformas candidate e abbina la prima il cui totale combacia con l'importo fattura.
     * Restituisce true se ha trovato e associato un match.
     */
    private function tryMatchByAmount(\Illuminate\Support\Collection $proformas, PurchaseInvoice $purchaseInvoice, array &$stats): bool
    {
        $invoiceAmount = (float) $purchaseInvoice->amount;

        foreach ($proformas as $proforma) {
            if ($this->amountsMatch($invoiceAmount, (float) $proforma->totale)) {
                $this->associateProformaWithInvoice($proforma, $purchaseInvoice);
                $stats['matched_proformas']++;
                Log::info("[PurchaseMatching] Proforma {$proforma->id} ↔ PurchaseInvoice {$purchaseInvoice->id} — importo: {$invoiceAmount}");
                return true;
            }
        }

        return false;
    }

    /**
     * Confronta due importi con una tolleranza per differenze floating-point.
     */
    private function amountsMatch(float $amount1, float $amount2, float $tolerance = 5): bool
    {
        return abs($amount1 - $amount2) <= $tolerance;
    }

    /**
     * Associa la proforma alla fattura in una transazione atomica.
     *
     * - Imposta invoiceable_type / invoiceable_id sulla proforma
     * - Marca le provvigioni collegate come 'Pagato'
     * - Chiude la PurchaseInvoice
     */
    private function associateProformaWithInvoice(Proforma $proforma, PurchaseInvoice $purchaseInvoice): void
    {
        DB::transaction(function () use ($proforma, $purchaseInvoice) {
            $proforma->update([
                'invoiceable_type' => PurchaseInvoice::class,
                'invoiceable_id'   => $purchaseInvoice->id,
            ]);

            $proforma->provvigioni()->update([
                'stato' => 'Pagato',
            ]);

            $purchaseInvoice->update([
                'closed' => true,
            ]);
        });
    }

    /**
     * Restituisce statistiche sui record non ancora abbinati.
     *
     * @return array{unmatched_proformas: int, unmatched_invoices: int}
     */
    public function getUnmatchedStatistics(): array
    {
        $unmatchedProformas = Proforma::whereNull('invoiceable_id')
            ->whereNotNull('sended_at')
            ->count();

        $unmatchedInvoices = PurchaseInvoice::where('closed', false)
            ->whereNotNull('vat_number')
            ->whereNotNull('amount')
            ->count();

        return [
            'unmatched_proformas' => $unmatchedProformas,
            'unmatched_invoices'  => $unmatchedInvoices,
        ];
    }
}
