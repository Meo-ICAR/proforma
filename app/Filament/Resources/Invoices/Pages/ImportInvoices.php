<?php
namespace App\Imports;

use App\Models\Fornitore;
use App\Models\Invoice;
use App\Models\Proforma;
use Illuminate\Support\Carbon;
// use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InvoicesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $piva = $row['partita_iva'];
        $fornitore = Fornitore::where('piva', $piva)->exists();
        // Se non c'è fornitore, ritorna null (la riga viene ignorata)
        if (!$fornitore) {
            return null;
        }
        $nrDoc = $row['nr_documento'];
        $giaPresente = Invoice::where('nr_documento', $nrDoc)
            ->where('fornitore_piva', $piva)
            ->exists();

        if ($giaPresente) {
            return null;  // Ignora duplicati
        }
        $datadocx = $this->transformDate($row['data_documento_fornitore']);
        $datadoc = \Carbon\Carbon::parse($datadocx);
        $competenza = $datadoc->year;
        $minDate = \Carbon\Carbon::create($competenza, 1, 15);  // 15 Gennaio 2025
        if ($datadoc < $minDate) {
            $competenza = $competenza - 1;
        }

        $uno = 1;
        if ($row['tipo_di_documento'] == 'Nota credito') {
            $uno = -1;
        }

        // Create the invoice data array

        $invoiceData = [
            'competenza' => $competenza,
            'fornitore_piva' => $piva,
            'fornitore' => $row['nome_fornitore'] ?? null,
            'invoice_number' => $row['nr_documento'] ?? null,
            'invoice_date' => $datadocx,
            'total_amount' => $uno * $this->transformDecimal($row['importo_totale_fornitore']),
            'tax_amount' => $uno * $this->transformDecimal($row['imponibile_iva']),
            'importo_iva' => $uno * $this->transformDecimal($row['importo_iva']),
            'importo_totale_fornitore' => $uno * $this->transformDecimal($row['importo_totale_fornitore']),
        ];

        // Create the invoice model
        $invoice = Invoice::create($invoiceData);

        if ($invoice) {
            $matchingProformas = $invoice
                ->relatedProformas()
                ->where('compenso', $invoice->total_amount)
                ->get();
            if ($matchingProformas->isNotEmpty()) {
                // If you want to associate the first matching proforma with the invoice
                $proforma = $matchingProformas->first();
                $invoice->isreconiled = true;  // Assuming you have this column
                $invoice->save();
                // Optional: Mark the proforma as paid or update its status
                $proforma->update(['paid_at' => $datadocx, 'stato' => 'Pagato']);
            }
            $invoice->save();

            return $invoice;
        }
        return null;
    }

    public function headingRow(): int
    {
        return 1;  // If your Excel has headers in the first row
    }

    /**
     * Converte le date Excel (numeriche) o stringhe in formato Y-m-d
     */
    private function transformDate($value)
    {
        if (empty($value))
            return null;
        try {
            return is_numeric($value)
                ? Date::excelToDateTimeObject($value)->format('Y-m-d')
                : Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Converte datetime (Data e Ora)
     */
    private function transformDateTime($value)
    {
        if (empty($value))
            return null;
        try {
            return is_numeric($value)
                ? Date::excelToDateTimeObject($value)->format('Y-m-d H:i:s')
                : Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Gestisce i decimali (rimuove eventuali virgole italiane)
     */
    private function transformDecimal($value)
    {
        if (empty($value))
            return 0.0;
        $clean = str_replace(',', '.', $value);
        return (float) filter_var($clean, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Converte Sì/No o 1/0 in booleano per tinyint
     */
    private function transformBoolean($value)
    {
        $value = strtolower(trim($value));
        return in_array($value, ['1', 'si', 'sì', 'true', 'yes', '=VERO()', 'VERO()']) ? 1 : 0;
    }
}
