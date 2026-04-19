<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use App\Models\Fornitore;
use App\Models\Provvigione;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class PurchaseInvoiceMatchingService
{
    private $companyId = null;

    /**
     * Set company ID for filtering
     */
    public function setCompanyId(?string $companyId): void
    {
        $this->companyId = Company::first()->id;
    }

    /**
     * Match Purchase invoices with Fornitore using VAT number
     *
     * @param array $options Matching options
     * @return array Matching results
     */
    public function matchPurchaseInvoices(array $options = []): array
    {
        $defaultOptions = [
            'dry_run' => false,
            'update_existing' => false,
            'batch_size' => 100
        ];

        $options = array_merge($defaultOptions, $options);

        $results = [
            'matched' => 0,
            'unmatched' => 0,
            'updated' => 0,
            'errors' => 0,
            'details' => []
        ];

        // Get all Purchase invoices that need matching
        $PurchaseInvoices = $this->getPurchaseInvoicesToMatch();

        foreach ($PurchaseInvoices as $invoice) {
            try {
                $matchResult = $this->matchSingleInvoice($invoice, $options);

                if ($matchResult['matched']) {
                    $results['matched']++;

                    if (!$options['dry_run'] && $matchResult['update_data']) {
                        $this->updateInvoiceWithMatch($invoice, $matchResult['update_data']);
                        $results['updated']++;
                    }

                    $results['details'][] = [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->number,
                        'vat_number' => $invoice->vat_number,
                        'matched_to' => $matchResult['matched_to'],
                        'match_type' => $matchResult['match_type']
                    ];
                } else {
                    $results['unmatched']++;
                    $results['details'][] = [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->number,
                        'vat_number' => $invoice->vat_number,
                        'reason' => $matchResult['reason'] ?? 'No suitable match found'
                    ];
                }
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error("Error matching invoice {$invoice->id}: " . $e->getMessage());
                $results['details'][] = [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Get Purchase invoices that need matching
     */
    private function getPurchaseInvoicesToMatch(): Collection
    {
        $query = PurchaseInvoice::whereNull('invoiceable_id')
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->whereNotNull('vat_number')
                        ->where('vat_number', '!=', '');
                })->orWhere(function ($subQuery) {
                    $subQuery
                        ->whereNull('vat_number')
                        ->orWhere('vat_number', '');
                });
            });

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        return $query->get();
    }

    /**
     * Match a single Purchase invoice with Fornitore or provvigioni
     */
    private function matchSingleInvoice(PurchaseInvoice $invoice, array $options): array
    {
        // If invoice has VAT number, try to match with Fornitore using VAT
        if (!empty($invoice->vat_number)) {
            $FornitoreMatch = $this->matchWithFornitoreByVat($invoice);
            if ($FornitoreMatch['matched']) {
                return $FornitoreMatch;
            }
            // match con nome cliente
            $FornitoreMatch = $this->matchWithFornitoreByName($invoice);
            if ($FornitoreMatch['matched']) {
                // Update the Fornitore model with VAT number
                $Fornitore = Fornitore::find($FornitoreMatch['update_data']['invoiceable_id']);
                if ($Fornitore) {
                    $Fornitore->update(['piva' => $invoice->vat_number]);
                }
                return $FornitoreMatch;
            }
        }

        // If invoice has no VAT  number, try with Client model
        if (empty($invoice->vat_number)) {
            $FornitoreMatch = $this->matchWithClientByName($invoice);
            if ($FornitoreMatch['matched']) {
                $invoice->update(['closed' => true, 'is_notpractice' => true]);
                return $FornitoreMatch;
            }
        }

        return [
            'matched' => false,
            'reason' => 'No matching found for supplier: ' . $invoice->supplier
        ];
    }

    /**
     * Match Purchase invoice with Fornitore using VAT number (piva)
     */
    private function matchWithFornitoreByVat(PurchaseInvoice $invoice): array
    {
        $Fornitore = Fornitore::where('piva', $invoice->vat_number)
            //  ->where('is_active', 1)
            ->first();

        if ($Fornitore) {
            return [
                'matched' => true,
                'match_type' => 'vat_to_Fornitore',
                'matched_to' => 'Fornitore: ' . $Fornitore->name,
                'confidence' => 1.0,
                'update_data' => [
                    'invoiceable_type' => 'App\Models\Fornitore',
                    'invoiceable_id' => $Fornitore->id
                ]
            ];
        }

        $client = Client::where('vat_number', $invoice->vat_number)
            ->first();

        if ($client) {
            return [
                'matched' => true,
                'match_type' => 'vat_to_client',
                'matched_to' => 'Client: ' . $client->name,
                'confidence' => 1.0,
                'update_data' => [
                    'invoiceable_type' => 'App\Models\Client',
                    'invoiceable_id' => $client->id
                ]
            ];
        }

        return [
            'matched' => false,
            'confidence' => 0,
            'reason' => 'No Fornitore found with VAT: ' . $invoice->vat_number
        ];
    }

    /**
     * Match Purchase invoice with Fornitore by name (direct match)
     */
    private function matchWithFornitoreByName(PurchaseInvoice $invoice): array
    {
        $Fornitore = Fornitore::where('name', $invoice->supplier)
            ->first();

        if ($Fornitore) {
            return [
                'matched' => true,
                'match_type' => 'name_to_Fornitore',
                'matched_to' => 'Fornitore: ' . $Fornitore->name,
                'confidence' => 1.0,
                'update_data' => [
                    'invoiceable_type' => 'App\Models\Fornitore',
                    'invoiceable_id' => $Fornitore->id
                ]
            ];
        }

        return [
            'matched' => false,
            'confidence' => 0,
            'reason' => 'No Fornitore found with name: ' . $invoice->supplier
        ];
    }

    /**
     * Match Purchase invoice with Fornitore by name (direct match)
     */
    private function matchWithClientByName(PurchaseInvoice $invoice): array
    {
        // Normalize whitespace: convert multiple spaces to single space and trim
        $normalizedName = preg_replace('/\s+/', ' ', trim($invoice->supplier));

        // Try exact match first
        $client = Client::where('name', $invoice->supplier)
            ->first();

        // If no exact match, try with normalized whitespace
        if (!$client) {
            $client = Client::whereRaw("REGEXP_REPLACE(TRIM(name), '[[:space:]]+', ' ') = ?", [$normalizedName])
                ->first();
        }

        // If still no match, try with LIKE for more flexible matching
        if (!$client) {
            $client = Client::where('name', 'LIKE', '%' . $normalizedName . '%')
                ->first();
        }

        if ($client) {
            return [
                'matched' => true,
                'match_type' => 'name_to_client',
                'matched_to' => 'Client: ' . $client->name,
                'confidence' => 1.0,
                'update_data' => [
                    'invoiceable_type' => 'App\Models\Client',
                    'invoiceable_id' => $client->id
                ]
            ];
        }

        return [
            'matched' => false,
            'confidence' => 0,
            'reason' => 'No client found with name: ' . $invoice->supplier
        ];
    }

    /**
     * Match Purchase invoice with provvigioni using Client name
     */
    private function matchWithProvvigioneByClientName(PurchaseInvoice $invoice): array
    {
        // Import Provvigione model
        $provvigione = Provvigione::where('tipo', 'Agente')
            ->where('denominazione_riferimento', $invoice->supplier)
            ->whereNull('annullato')
            ->first();

        if ($provvigione && $provvigione->cliente) {
            if (empty($provvigione->cliente)) {
                Client::insert([
                    'name' => $provvigione->denominazione_riferimento,
                    'company_id' => $companyId,
                    'is_company' => 1,
                    'is_lead' => 0,
                    'is_client' => 0
                ]);
            }
            return [
                'matched' => true,
                'match_type' => 'provvigione_to_Fornitore',
                'matched_to' => 'Fornitore: ' . $provvigione->cliente->name,
                'confidence' => 1.0,
                'update_data' => [
                    'invoiceable_type' => 'App\Models\Fornitore',
                    'invoiceable_id' => $provvigione->cliente->id
                ]
            ];
        }

        return [
            'matched' => false,
            'confidence' => 0,
            'reason' => 'No provvigione found for Client: ' . $invoice->supplier
        ];
    }

    /**
     * Update Purchase invoice with match data
     */
    private function updateInvoiceWithMatch(PurchaseInvoice $invoice, array $updateData): void
    {
        $invoice->update($updateData);
    }

    /**
     * Get matching statistics
     */
    public function getMatchingStats(): array
    {
        $baseQuery = PurchaseInvoice::query();

        if ($this->companyId) {
            $baseQuery->where('company_id', $this->companyId);
        }

        $totalToMatch = $baseQuery->whereNull('invoiceable_id')->count();

        $matchedQuery = PurchaseInvoice::query();
        if ($this->companyId) {
            $matchedQuery->where('company_id', $this->companyId);
        }

        $stats = [
            'total_invoices' => $baseQuery->count(),
            'total_to_match' => $totalToMatch,
            'unmatched_invoices' => $this->getPurchaseInvoicesToMatch()->count(),
            'matched_invoices' => $matchedQuery
                ->whereNotNull('invoiceable_type')
                ->whereNotNull('invoiceable_id')
                ->count(),
            'Fornitore_matches' => $matchedQuery->where('invoiceable_type', 'App\Models\Fornitore')->count(),
        ];

        $stats['match_percentage'] = $totalToMatch > 0
            ? round(($stats['matched_invoices'] / $totalToMatch) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Execute matching for Purchase invoices (called from Filament action)
     */
    public function matchFornitoresByVatNumber(): array
    {
        $options = [
            'dry_run' => false,
        ];

        return $this->matchPurchaseInvoices($options);
    }

    /**
     * Clear existing matches (for re-matching)
     */
    public function clearMatches(): int
    {
        $query = PurchaseInvoice::where('invoiceable_type', 'App\Models\Fornitore');

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        return $query->update([
            'invoiceable_type' => null,
            'invoiceable_id' => null
        ]);
    }
}
