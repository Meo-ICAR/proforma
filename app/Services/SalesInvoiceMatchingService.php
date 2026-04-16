<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Clienti;
use App\Models\Company;
use App\Models\Provvigione;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class SalesInvoiceMatchingService
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
     * Match sales invoices with clienti using VAT number
     *
     * @param array $options Matching options
     * @return array Matching results
     */
    public function matchSalesInvoices(array $options = []): array
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

        // Get all sales invoices that need matching
        $salesInvoices = $this->getSalesInvoicesToMatch();

        foreach ($salesInvoices as $invoice) {
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
     * Get sales invoices that need matching
     */
    private function getSalesInvoicesToMatch(): Collection
    {
        $query = SalesInvoice::whereNull('invoiceable_id')
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
     * Match a single sales invoice with clienti or provvigioni
     */
    private function matchSingleInvoice(SalesInvoice $invoice, array $options): array
    {
        // If invoice has VAT number, try to match with clienti using VAT
        if (!empty($invoice->vat_number)) {
            $clientiMatch = $this->matchWithClientiByVat($invoice);
            if ($clientiMatch['matched']) {
                return $clientiMatch;
            }
            // match con nome cliente
            $clientiMatch = $this->matchWithClientiByName($invoice);
            if ($clientiMatch['matched']) {
                // Update the Clienti model with VAT number
                $clienti = Clienti::find($clientiMatch['update_data']['invoiceable_id']);
                if ($clienti) {
                    $clienti->update(['piva' => $invoice->vat_number]);
                }
                return $clientiMatch;
            }
        }

        // If invoice has no VAT  number, try with Client model
        if (empty($invoice->vat_number)) {
            $clientiMatch = $this->matchWithCustomerByName($invoice);
            if ($clientiMatch['matched']) {
                return $clientiMatch;
            }
        }

        return [
            'matched' => false,
            'reason' => 'No matching found for customer: ' . $invoice->customer_name
        ];
    }

    /**
     * Match sales invoice with clienti using VAT number (piva)
     */
    private function matchWithClientiByVat(SalesInvoice $invoice): array
    {
        $clienti = Clienti::where('piva', $invoice->vat_number)
            ->where('is_active', 1)
            ->first();

        if ($clienti) {
            return [
                'matched' => true,
                'match_type' => 'vat_to_clienti',
                'matched_to' => 'Clienti: ' . $clienti->name,
                'confidence' => 1.0,
                'update_data' => [
                    'invoiceable_type' => 'App\Models\Clienti',
                    'invoiceable_id' => $clienti->id
                ]
            ];
        }

        return [
            'matched' => false,
            'confidence' => 0,
            'reason' => 'No clienti found with VAT: ' . $invoice->vat_number
        ];
    }

    /**
     * Match sales invoice with clienti by name (direct match)
     */
    private function matchWithClientiByName(SalesInvoice $invoice): array
    {
        $clienti = Clienti::where('name', $invoice->customer_name)
            ->first();

        if ($clienti) {
            return [
                'matched' => true,
                'match_type' => 'name_to_clienti',
                'matched_to' => 'Clienti: ' . $clienti->name,
                'confidence' => 1.0,
                'update_data' => [
                    'invoiceable_type' => 'App\Models\Clienti',
                    'invoiceable_id' => $clienti->id
                ]
            ];
        }

        return [
            'matched' => false,
            'confidence' => 0,
            'reason' => 'No clienti found with name: ' . $invoice->customer_name
        ];
    }

    /**
     * Match sales invoice with clienti by name (direct match)
     */
    private function matchWithCustomerByName(SalesInvoice $invoice): array
    {
        // Normalize whitespace: convert multiple spaces to single space and trim
        $normalizedName = preg_replace('/\s+/', ' ', trim($invoice->customer_name));

        // Try exact match first
        $client = Client::where('name', $invoice->customer_name)
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
                'matched_to' => 'Customer: ' . $client->name,
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
            'reason' => 'No client found with name: ' . $invoice->customer_name
        ];
    }

    /**
     * Match sales invoice with provvigioni using customer name
     */
    private function matchWithProvvigioneByCustomerName(SalesInvoice $invoice): array
    {
        // Import Provvigione model
        $provvigione = \App\Models\Provvigione::where('tipo', 'Cliente')
            ->where('denominazione_riferimento', $invoice->customer_name)
            ->whereNull('annullato')
            ->first();

        if ($provvigione && $provvigione->cliente) {
            if (empty($provvigione->cliente)) {
                Client::insert([
                    'name' => $provvigione->denominazione_riferimento,
                    'company_id' => $companyId,
                    'is_company' => 0,
                    'is_lead' => 0,
                    'is_client' => 1
                ]);
            }
            return [
                'matched' => true,
                'match_type' => 'provvigione_to_clienti',
                'matched_to' => 'Clienti: ' . $provvigione->cliente->name,
                'confidence' => 1.0,
                'update_data' => [
                    'invoiceable_type' => 'App\Models\Clienti',
                    'invoiceable_id' => $provvigione->cliente->id
                ]
            ];
        }

        return [
            'matched' => false,
            'confidence' => 0,
            'reason' => 'No provvigione found for customer: ' . $invoice->customer_name
        ];
    }

    /**
     * Update sales invoice with match data
     */
    private function updateInvoiceWithMatch(SalesInvoice $invoice, array $updateData): void
    {
        $invoice->update($updateData);
    }

    /**
     * Get matching statistics
     */
    public function getMatchingStats(): array
    {
        $baseQuery = SalesInvoice::query();

        if ($this->companyId) {
            $baseQuery->where('company_id', $this->companyId);
        }

        $totalToMatch = $baseQuery->whereNull('invoiceable_id')->count();

        $matchedQuery = SalesInvoice::query();
        if ($this->companyId) {
            $matchedQuery->where('company_id', $this->companyId);
        }

        $stats = [
            'total_invoices' => $baseQuery->count(),
            'total_to_match' => $totalToMatch,
            'unmatched_invoices' => $this->getSalesInvoicesToMatch()->count(),
            'matched_invoices' => $matchedQuery
                ->whereNotNull('invoiceable_type')
                ->whereNotNull('invoiceable_id')
                ->count(),
            'clienti_matches' => $matchedQuery->where('invoiceable_type', 'App\Models\Clienti')->count(),
        ];

        $stats['match_percentage'] = $totalToMatch > 0
            ? round(($stats['matched_invoices'] / $totalToMatch) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Execute matching for sales invoices (called from Filament action)
     */
    public function matchClientisByVatNumber(): array
    {
        $options = [
            'dry_run' => false,
        ];

        return $this->matchSalesInvoices($options);
    }

    /**
     * Clear existing matches (for re-matching)
     */
    public function clearMatches(): int
    {
        $query = SalesInvoice::where('invoiceable_type', 'App\Models\Clienti');

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        return $query->update([
            'invoiceable_type' => null,
            'invoiceable_id' => null
        ]);
    }
}
