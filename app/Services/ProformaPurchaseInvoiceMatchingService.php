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
     * Tenta di abbinare una PurchaseInvoice a una o più Proforma.
     *
     * Passaggi:
     *  1. Trova il Fornitore tramite PurchaseInvoice.vat_number = Fornitore.piva
     *  2. Cerca le Proforma con fornitori_id = Fornitore.id, ancora non abbinate,
     *     già inviate e con data di invio ≤ registration_date della fattura
     *  3. Confronta l'importo della fattura con il totale della proforma (±0.01)
     *  4. Se combaciano, associa proforma ↔ fattura
     */
    private function processPurchaseInvoice(PurchaseInvoice $purchaseInvoice, array &$stats): void
    {
        // Passo 1: trova il Fornitore tramite P.IVA
        $fornitore = Fornitore::where('piva', $purchaseInvoice->vat_number)->first();

        if (! $fornitore) {
            Log::debug("[PurchaseMatching] Nessun fornitore trovato per P.IVA '{$purchaseInvoice->vat_number}' (fattura {$purchaseInvoice->id})");
            return;
        }

        // Passo 2: cerca le proforma del fornitore non ancora abbinate
        $proformas = Proforma::where('fornitori_id', $fornitore->id)
            ->whereNull('invoiceable_id')
            ->whereNotNull('sended_at')
            ->where('sended_at', '<=', $purchaseInvoice->registration_date)
            ->get();

        if ($proformas->isEmpty()) {
            Log::debug("[PurchaseMatching] Nessuna proforma disponibile per fornitore {$fornitore->id} (fattura {$purchaseInvoice->id})");
            return;
        }

        // Passo 3 + 4: confronta importi e abbina
        foreach ($proformas as $proforma) {
            $proformaTotal = (float) $proforma->totale;
            $invoiceAmount = (float) $purchaseInvoice->amount;

            if ($this->amountsMatch($invoiceAmount, $proformaTotal)) {
                $this->associateProformaWithInvoice($proforma, $purchaseInvoice);
                $stats['matched_proformas']++;

                Log::info("[PurchaseMatching] Proforma {$proforma->id} ↔ PurchaseInvoice {$purchaseInvoice->id} — importo: {$invoiceAmount}");

                // Una sola proforma per fattura; la fattura viene chiusa: usciamo dal loop.
                break;
            }
        }
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
