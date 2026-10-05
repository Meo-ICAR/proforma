<?php
namespace App\Imports;

use App\Models\Invoicein;
use Illuminate\Support\Carbon;
// use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InvoiceinsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Invoicein([
            'tipo_di_documento' => $row['tipo_di_documento'] ?? null,
            'nr_documento' => $row['nr_documento'] ?? null,
            'nr_fatt_acq_registrata' => $row['nr_fatt_acq_registrata'] ?? null,
            'nr_nota_cr_acq_registrata' => $row['nr_nota_cr_acq_registrata'] ?? null,
            'data_ricezione_fatt' => $this->transformDate($row['data_ricezione_fatt']),
            'codice_td' => $row['codice_td'] ?? null,
            'nr_cliente_fornitore' => $row['nr_cliente_fornitore'] ?? null,
            'nome_fornitore' => $row['nome_fornitore'] ?? null,
            'partita_iva' => $row['partita_iva'] ?? null,
            'nr_documento_fornitore' => $row['nr_documento_fornitore'] ?? null,
            'allegato' => $this->transformBoolean($row['allegato']) ?? null,
            'data_documento_fornitore' => $this->transformDate($row['data_documento_fornitore']),
            'data_primo_pagamento_prev' => $this->transformDate($row['data_primo_pagamento_prev']),
            'imponibile_iva' => $this->transformDecimal($row['imponibile_iva']),
            'importo_iva' => $this->transformDecimal($row['importo_iva']),
            'importo_totale_fornitore' => $this->transformDecimal($row['importo_totale_fornitore']),
            'importo_totale_collegato' => $this->transformDecimal($row['importo_totale_collegato']),
            //     'data_ora_invio_ricezione' => $this->transformDateTime($row['data_ora_invio_ricezione']),
            'stato' => $row['stato'] ?? null,
            'id_documento' => $row['id_documento'] ?? null,
            'id_sdi' => $row['id_sdi'] ?? null,
            'nr_lotto_documento' => $row['nr_lotto_documento'] ?? null,
            'nome_file_doc_elettronico' => $row['nome_file_doc_elettronico'] ?? null,
            'filtro_carichi' => $row['filtro_carichi'] ?? null,
            'cdc_codice' => $row['cdc_codice'] ?? null,
            'cod_colleg_dimen_2' => $row['cod_colleg_dimen_2'] ?? null,
            'allegato_in_file_xml' => $this->transformBoolean($row['allegato_in_file_xml']),
            'note_1' => $row['note_1'] ?? null,
            'note_2' => $row['note_2'] ?? null,
        ]);
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
        return in_array($value, ['1', 'si', 'sì', 'true', 'yes', '=VERO()']) ? 1 : 0;
    }
}
