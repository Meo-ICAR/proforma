<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Client;
use App\Models\PracticeCommission;
use App\Models\Principal;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SalesInvoiceImportService
{
    protected $companyId;
    protected $filename;

    protected $importResults = [
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'details' => []
    ];

    public function setCompanyId($companyId): void
    {
        $this->companyId = Company::first()->id;
    }

    public function __construct($filename = null)
    {
        $this->filename = $filename;
    }

    public function import($filePath, $companyId)
    {
        $this->companyId = Company::first()->id;

        // Extract filename from path if not provided
        if (!$this->filename) {
            $this->filename = basename($filePath);
        }

        $this->importResults = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'details' => [],
            'filename' => $this->filename,
        ];

        // Handle storage path
        $actualFilePath = $filePath;
        if (!file_exists($filePath)) {
            // Try storage path if public path doesn't exist
            $storagePath = str_replace('public/', 'storage/app/private/sales-invoice-imports/', $filePath);
            if (file_exists($storagePath)) {
                $actualFilePath = $storagePath;
                Log::info('File found in storage path', ['path' => $storagePath]);
            } else {
                throw new \Exception("File not found: {$filePath} (tried: {$storagePath})");
            }
        }

        DB::beginTransaction();

        try {
            // Read Excel file using Excel facade
            $data = Excel::toArray([], $actualFilePath);

            if (empty($data) || empty($data[0])) {
                throw new \Exception('Cannot read data from Excel file');
            }

            $rows = $data[0];
            $headers = array_shift($rows);  // Remove first row as headers

            if (empty($headers)) {
                throw new \Exception('Cannot read headers from file');
            }

            // Clean headers - remove special characters and normalize
            $cleanHeaders = [];
            foreach ($headers as $header) {
                $cleanHeader = trim($header);
                // Remove BOM if present
                $cleanHeader = str_replace("\u{FEFF}", '', $cleanHeader);
                // Replace special chars but keep dots and meaningful characters
                $cleanHeader = str_replace([' ', '-', '(', ')', '/', '°'], ['_', '_', '_', '_', '_'], $cleanHeader);
                $cleanHeaders[] = $cleanHeader;
            }

            Log::info('Sales Invoice Excel Headers', ['original' => $headers, 'cleaned' => $cleanHeaders, 'file_type' => pathinfo($actualFilePath, PATHINFO_EXTENSION)]);

            $rowNumber = 2;  // Start from 2 since we already read header

            foreach ($rows as $row) {
                $this->processRow($row, $cleanHeaders, $rowNumber);
                $rowNumber++;
            }

            DB::commit();

            /*
             * // 1. Definiamo la subquery (la tabella derivata con i calcoli)
             * $subquery = PracticeCommission::select([
             *     'practice_commissions.invoice_number',
             *     DB::raw('YEAR(practice_commissions.invoice_at) as invoice_year'),
             *     'practice_commissions.is_payment',
             *     'sales_invoices.number as matched_s_number'
             * ])
             *     ->join('sales_invoices', function ($join) {
             *         $join->on('sales_invoices.number', 'like', DB::raw("CONCAT('%', SUBSTR(CONCAT('00000', practice_commissions.invoice_number), -5))"));
             *     })
             *     ->where('practice_commissions.invoice_number', '>', '0')
             *     ->where('practice_commissions.is_payment', 0)
             *     ->where('sales_invoices.number', 'like', 'FVI2%')
             *     ->groupBy(
             *         'practice_commissions.invoice_number',
             *         DB::raw('YEAR(practice_commissions.invoice_at)'),
             *         'practice_commissions.is_payment',
             *         'sales_invoices.number',
             *         'sales_invoices.amount'
             *     )
             *     ->havingRaw('sales_invoices.amount = SUM(practice_commissions.amount)');
             *
             * // 2. Eseguiamo l'UPDATE unendo la tabella principale con la subquery
             * DB::table('practice_commissions as p')
             *     ->joinSub($subquery, 'dati_calcolati', function ($join) {
             *         $join
             *             ->on('p.invoice_number', '=', 'dati_calcolati.invoice_number')
             *             ->whereRaw('YEAR(p.invoice_at) = dati_calcolati.invoice_year')  // whereRaw per gestire la funzione YEAR()
             *             ->on('p.is_payment', '=', 'dati_calcolati.is_payment');
             *     })
             *     ->update([
             *         // Usiamo DB::raw affinché Laravel non lo tratti come una semplice stringa di testo
             *         'p.alternative_number_invoice' => DB::raw('dati_calcolati.matched_s_number')
             *     ]);
             */

            /*
             * use Illuminate\Support\Facades\DB;
             *
             * // 1. Prepariamo la subquery esattamente come l'hai definita
             * $subquery = DB::table('sales_invoices as s_sub')
             *     ->select([
             *         'c_sub.principal_id',
             *         'c_sub.invoice_at',
             *         's_sub.number as matched_sales_invoice_number'
             *     ])
             *     ->join('principals as p_sub', 'p_sub.vat_number', '=', 's_sub.vat_number')
             *     ->join('practice_commissions as c_sub', function($join) {
             *         $join->on('c_sub.principal_id', '=', 'p_sub.id')
             *              ->on('c_sub.invoice_at', '=', 's_sub.registration_date');
             *     })
             *     ->where('c_sub.tipo', 'Istituto')
             *     ->groupBy([
             *         'c_sub.principal_id',
             *         'c_sub.invoice_at',
             *         's_sub.number',
             *         's_sub.amount'
             *     ])
             *     ->havingRaw('s_sub.amount = SUM(c_sub.amount)');
             *
             * // 2. Eseguiamo l'UPDATE unendo la tabella principale
             * DB::table('practice_commissions as c')
             *     ->joinSub($subquery, 'dati_calcolati', function ($join) {
             *         $join->on('c.principal_id', '=', 'dati_calcolati.principal_id')
             *              ->on('c.invoice_at', '=', 'dati_calcolati.invoice_at');
             *     })
             *     // Eseguiamo l'update sul campo corretto
             *     ->update([
             *         'c.alternative_number_invoice' => DB::raw('dati_calcolati.matched_sales_invoice_number')
             *     ]);
             */
            Log::info('Sales invoices import completed', [
                'file' => $filePath,
                'company_id' => $this->companyId,
                'results' => $this->importResults
            ]);

            return $this->importResults;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing sales invoices', [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->importResults['errors']++;
            $this->importResults['details'][] = 'Error processing file: ' . $e->getMessage();

            return $this->importResults;
        }
    }

    protected function processRow(array $row, array $headers, int $rowNumber)
    {
        try {
            // Use direct index mapping instead of array_combine for reliability
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = $row[$index] ?? '';
            }

            // Skip empty rows - using Excel format field names
            if (empty($rowData['Nr.']) || empty($rowData['Ragione_Sociale'])) {
                Log::info('Skipping row due to empty data', [
                    'row_number' => $rowNumber,
                    'Nr.' => $rowData['Nr.'] ?? 'NULL',
                    'Ragione_Sociale' => $rowData['Ragione_Sociale'] ?? 'NULL',
                    'raw_row' => $rowData
                ]);
                $this->importResults['skipped']++;
                return;
            }

            $invoiceData = $this->mapRowToInvoiceData($rowData);

            // Add company_id
            $invoiceData['company_id'] = $this->companyId;

            // Check if invoice already exists
            $existingInvoice = SalesInvoice::where('company_id', $this->companyId)
                ->where('number', $invoiceData['number'])
                ->first();

            // Always create new invoice for 2025 (don't update existing)
            if (!$existingInvoice) {
                $invoice = SalesInvoice::create($invoiceData);
                $this->importResults['imported']++;
                $this->importResults['details'][] = "Imported invoice: {$invoiceData['number']} (row {$rowNumber})";
            } else {
                $this->importResults['skipped']++;
                $this->importResults['details'][] = "Skipped existing invoice: {$invoiceData['number']} (row {$rowNumber})";
            }
        } catch (\Exception $e) {
            Log::error('Error processing row', [
                'row_number' => $rowNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->importResults['errors']++;
            $this->importResults['details'][] = "Error processing row {$rowNumber}: " . $e->getMessage();
        }
    }

    protected function mapRowToInvoiceData(array $row): array
    {
        return [
            'number' => $this->cleanString($row['Nr.'] ?? null),
            'order_number' => $this->cleanString($row['Nr._ordine'] ?? null),
            'customer_number' => $this->cleanString($row['Nr._cliente'] ?? null),
            'customer_name' => $this->cleanString($row['Ragione_Sociale'] ?? null),
            'currency_code' => $this->cleanString($row['Cod._valuta'] ?? null),
            'due_date' => $this->parseDate($row['Data_scadenza'] ?? null),
            'amount' => $this->parseDecimal($row['Importo'] ?? null),
            'amount_including_vat' => $this->parseDecimal($row['Importo_IVA_inclusa'] ?? null),
            'residual_amount' => $this->parseDecimal($row['Importo_residuo'] ?? null),
            'ship_to_code' => $this->cleanString($row['Spedire_a___Codice'] ?? null),
            'ship_to_cap' => $this->cleanString($row['Spedire_a___CAP'] ?? null),
            'registration_date' => $this->parseDate($row['Data_di_registrazione']) ?? now()->format('Y-m-d'),
            'agent_code' => $this->cleanString($row['Cod._agente'] ?? null),
            'cdc_code' => $this->cleanString($row['Cdc_Codice'] ?? null),
            'dimensional_link_code' => $this->cleanString($row['Cod._colleg._dimen._2'] ?? null),
            'location_code' => $this->cleanString($row['Cod._ubicazione'] ?? null),
            'printed_copies' => $this->parseInteger($row['Copie_stampate'] ?? 0),
            'payment_condition_code' => $this->cleanString($row['Cod._condizioni_pagam.'] ?? null),
            'closed' => $this->parseBoolean($row['Chiuso'] ?? null),
            'cancelled' => $this->parseBoolean($row['Annullato'] ?? null),
            'corrected' => $this->parseBoolean($row['Rettifica'] ?? null),
            'email_sent' => $this->parseBoolean($row['E_mail_inviata'] ?? null),
            'email_sent_at' => $this->parseDateTime($row['Data_ora_invio_mail'] ?? null),
            'bill_to_address' => $this->cleanString($row['Fatturare_a___Indirizzo'] ?? null),
            'bill_to_city' => $this->cleanString($row['Fatturare_a___Città'] ?? null),
            'bill_to_province' => $this->cleanString($row['Provincia_di_fatturazione'] ?? null),
            'ship_to_address' => $this->cleanString($row['Spedire_a___Indirizzo'] ?? null),
            'ship_to_city' => $this->cleanString($row['Spedire_a___Città'] ?? null),
            'payment_method_code' => $this->cleanString($row['Cod._metodo_di_pagamento'] ?? null),
            'customer_category' => $this->cleanString($row['Cat._reg._cliente'] ?? null),
            'exchange_rate' => $this->parseDecimal($row['Fattore_valuta'] ?? null),
            'vat_number' => $this->cleanString($row['Partita_IVA'] ?? null),
            'bank_account' => $this->cleanString($row['C_C_bancario'] ?? null),
            'document_residual_amount' => $this->parseDecimal($row['Importo_residuo_documento'] ?? null),
            'document_type' => $this->cleanString($row['Tipo_di_documento_Fattura'] ?? null),
            'credit_note_linked' => $this->cleanString($row['Nota_di_Credito_Collegata'] ?? null),
            'in_order' => $this->parseBoolean($row['Flg_In_Commessa'] ?? null),
            'supplier_number' => $this->cleanString($row['Nr._fornitore'] ?? null),
            'supplier_description' => $this->cleanString($row['Descrizione_Fornitore'] ?? null),
            'purchase_invoice_origin' => $this->cleanString($row['Fattura_Acquisto_Origine'] ?? null),
            'sent_to_sdi' => $this->parseBoolean($row['Inviato_allo_SDI'] ?? null),
        ];
    }

    protected function cleanString($value)
    {
        if (empty($value)) {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    protected function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Handle Italian date formats
        $dateFormats = [
            'd/m/Y', 'd/m/Y', 'd-m-Y', 'd/m/Y',
            'd/m/y', 'd-m-y', 'Y-m-d'
        ];

        foreach ($dateFormats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Continue to next format
            }
        }

        // Try Excel serial date format
        if (is_numeric($value)) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                // Continue to return null
            }
        }

        return null;
    }

    protected function parseDateTime($value)
    {
        if (empty($value)) {
            return null;
        }

        // Handle Italian datetime formats
        $dateTimeFormats = [
            'd/m/Y H:i', 'd/m/Y H:i:s', 'd-m-Y H:i',
            'd/m/y H:i', 'd-m-y H:i', 'Y-m-d H:i:s'
        ];

        foreach ($dateTimeFormats as $format) {
            try {
                $dateTime = Carbon::createFromFormat($format, $value);
                if ($dateTime) {
                    return $dateTime->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                // Continue to next format
            }
        }

        return null;
    }

    protected function parseDecimal($value)
    {
        if (empty($value) || $value === '' || $value === null) {
            return 0;
        }

        // If it's already a float, return it directly
        if (is_float($value)) {
            return $value;
        }

        // Handle Italian format: 29.582,24 -> 29582.24
        // First, remove thousands separators (dots) only if there's a comma for decimal
        if (is_string($value) && strpos($value, ',') !== false) {
            $parts = explode(',', $value);
            $integer_part = str_replace('.', '', $parts[0]);
            $decimal_part = $parts[1] ?? '0';
            $value = $integer_part . '.' . $decimal_part;
        } else {
            // If no comma, just remove dots (might be thousands separators)
            $value = str_replace('.', '', $value);
        }

        return (float) $value;
    }

    protected function parseInteger($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    protected function parseBoolean($value)
    {
        if (empty($value)) {
            return null;
        }

        // Handle Excel TRUE/FALSE strings and formulas
        $value = strtoupper(trim($value));

        if ($value === 'TRUE' || $value === 'VERO' || $value === '=TRUE()') {
            return true;
        } elseif ($value === 'FALSE' || $value === 'FALSO' || $value === '=FALSE()') {
            return false;
        }

        return null;
    }

    public function getResults(): array
    {
        return $this->importResults;
    }
}
