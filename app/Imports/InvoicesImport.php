<?php

namespace App\Imports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class InvoicesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithChunkReading
{
    use Importable, SkipsErrors;

    public function model(array $row)
    {
        // Map your Excel columns to database fields
        return new Invoice([
            'fornitore_piva' => $row['piva_fornitore'] ?? null,
            'fornitore' => $row['ragione_sociale'] ?? null,
            'invoice_number' => $row['numero_fattura'] ?? null,
            'invoice_date' => $this->transformDate($row['data_fattura'] ?? null),
            'total_amount' => $row['importo_totale'] ?? 0,
            'tax_amount' => $row['imponibile'] ?? 0,
            'importo_iva' => $row['iva'] ?? 0,
            'status' => 'imported',
            // Add other fields as needed
        ]);
    }

    public function rules(): array
    {
        return [
            'numero_fattura' => 'required|string',
            'data_fattura' => 'required|date',
            'importo_totale' => 'required|numeric',
            'piva_fornitore' => 'required|string',
            'ragione_sociale' => 'required|string',
        ];
    }

    public function chunkSize(): int
    {
        return 1000; // Process in chunks of 1000 rows
    }

    /**
     * Convert Excel date to proper format
     */
    private function transformDate($value, $format = 'Y-m-d')
    {
        if (!$value) {
            return null;
        }

        if (is_numeric($value)) {
            // Convert Excel date to timestamp
            $unixDate = ($value - 25569) * 86400;
            return date($format, $unixDate);
        }

        try {
            return \Carbon\Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            return null;
        }
    }
}
