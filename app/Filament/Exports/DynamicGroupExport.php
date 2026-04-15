<?php

namespace App\Filament\Exports;

use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class DynamicGroupExport extends ExcelExport implements WithStyles
{
    protected ?string $groupBy = null;
    protected array $sumColumns = [];

    // L'hook corretto per pxlrbt/filament-excel è setUp(), non __construct o make()
    public function setUp(): void
    {
        $this
            ->fromTable()
            ->withFilename('report_' . now()->format('Y-m-d_H-i'));
        //    ->withEvents([
        //        AfterSheet::class => function (AfterSheet $event) {
        //            $this->processSheet($event);
        //      }
        //  ])
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],  // Style the first row (headings) as bold
            //   'B2' => ['font' => ['italic' => true]],  // Style a specific cell
            // cd   'C' => ['font' => ['size' => 16]],  // Style an entire column
        ];
    }

    public function groupBy(string $column): static
    {
        $this->groupBy = $column;
        return $this;
    }

    public function sumColumns(array $columns): static
    {
        $this->sumColumns = $columns;
        return $this;
    }

    // Tutta la logica spostata in un metodo dedicato per pulizia
    protected function processSheet(AfterSheet $event): void
    {
        $sheet = $event->sheet->getDelegate();

        // 1. GESTIONE FILTRI (Inserimento in cima)
        $livewire = $this->getLivewire();  // Usiamo $this invece di $event per prendere livewire
        $appliedFilters = $livewire->tableFilters ?? [];

        $filterStrings = [];
        foreach ($appliedFilters as $name => $data) {
            if (!empty($data['value'])) {
                $val = is_array($data['value']) ? implode(', ', $data['value']) : $data['value'];
                $filterStrings[] = strtoupper($name) . ': ' . $val;
            }
        }
        $filtersText = 'FILTRI APPLICATI: ' . (empty($filterStrings) ? 'Nessuno' : implode(' | ', $filterStrings));

        // Inseriamo spazio in alto (le intestazioni scivolano alla riga 3)
        $sheet->insertNewRowBefore(1, 2);
        $sheet->setCellValue('A1', $filtersText);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setItalic(true)->getColor()->setARGB('FF555555');

        // Se non c'è raggruppamento, abbiamo finito qui (i dati restano piatti ma coi filtri scritti)
        if (!$this->groupBy) {
            return;
        }

        // 2. GESTIONE RAGGRUPPAMENTO E SOMME
        // (Attenzione: le intestazioni ora sono alla riga 3, i dati iniziano alla riga 4)
        $headerRow = 3;
        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        if ($highestRow <= $headerRow)
            return;  // Niente dati da raggruppare

        $headings = $sheet->rangeToArray("A{$headerRow}:{$highestColumn}{$headerRow}", null, true, false)[0];
        $headingsLower = array_map('strtolower', $headings);

        $groupByIndex = array_search(strtolower($this->groupBy), $headingsLower);
        if ($groupByIndex === false)
            return;  // Colonna non trovata

        $sumIndices = [];
        foreach ($this->sumColumns as $col) {
            $idx = array_search(strtolower($col), $headingsLower);
            if ($idx !== false) {
                $sumIndices[] = $idx;
            }
        }

        // Leggiamo i dati (dalla riga 4 in poi)
        $dataStartRow = $headerRow + 1;
        $rows = $sheet->rangeToArray("A{$dataStartRow}:{$highestColumn}{$highestRow}", null, true, false);

        // Raggruppiamo
        $groups = [];
        foreach ($rows as $row) {
            $groupValue = $row[$groupByIndex] ?? '';
            $groups[$groupValue][] = $row;
        }

        // Cancelliamo le righe piatte originali
        $sheet->removeRow($dataStartRow, $highestRow - $headerRow);

        // Riscriviamo raggruppati con somme
        $currentRow = $dataStartRow;
        foreach ($groups as $groupName => $groupRows) {
            $groupSums = array_fill_keys($sumIndices, 0);

            foreach ($groupRows as $row) {
                foreach ($row as $colIdx => $value) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                    $sheet->setCellValue($colLetter . $currentRow, $value);
                }

                foreach ($sumIndices as $idx) {
                    $val = $row[$idx] ?? 0;
                    if (is_string($val)) {
                        $val = str_replace(['€', '.', ' '], '', $val);
                        $val = (float) str_replace(',', '.', $val);
                    }
                    $groupSums[$idx] += $val;
                }
                $currentRow++;
            }

            // Riga del totale
            $summaryRow = array_fill(0, $highestColumnIndex, '');
            $summaryRow[$groupByIndex] = 'TOTALE ' . strtoupper((string) $groupName);

            foreach ($sumIndices as $idx) {
                $summaryRow[$idx] = $groupSums[$idx];
            }

            foreach ($summaryRow as $colIdx => $value) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                $cell = $colLetter . $currentRow;
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }
            $currentRow++;  // Riga totale
            $currentRow++;  // Spazio bianco tra un gruppo e l'altro
        }
    }
}
