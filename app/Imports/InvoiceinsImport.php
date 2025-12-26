<?php

namespace App\Imports;

use App\Models\Invoicein;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InvoiceinsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Invoicein([
            // Map your Excel columns to model attributes
            // Example:
            // 'invoice_number' => $row['invoice_number'],
            // 'amount' => $row['amount'],
            // Add other fields as needed
        ]);
    }

    public function headingRow(): int
    {
        return 1;  // If your Excel has headers in the first row
    }
}
