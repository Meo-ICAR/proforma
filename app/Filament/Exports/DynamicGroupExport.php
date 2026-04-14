<?php

namespace App\Filament\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class DynamicGroupExport extends ExcelExport
{
    protected ?string $groupBy = null;
    protected array $sumColumns = [];

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'export')
            ->fromTable()  // Importante: mantiene i filtri della tabella
            ->withFilename('report_' . now()->format('Y-m-d_H-i'));
    }

    public function groupBy(string $column): self
    {
        $this->groupBy = $column;
        return $this;
    }

    public function sumColumns(array $columns): self
    {
        $this->sumColumns = $columns;
        return $this;
    }

    // Questa è la funzione chiave che manipola i dati prima della stampa
    public function transform(Collection $rows): Collection
    {
        $finalCollection = collect();

        // 1. RECUPERO E STAMPA DEI FILTRI
        $livewire = $this->getLivewire();
        $appliedFilters = $livewire->tableFilters ?? [];

        $filterDescription = [];
        foreach ($appliedFilters as $name => $data) {
            $value = $data['value'] ?? null;
            if ($value) {
                // Pulizia del nome del filtro e valore
                $filterDescription[] = strtoupper($name) . ': ' . (is_array($value) ? implode(', ', $value) : $value);
            }
        }

        $finalCollection->push(['FILTRI APPLICATI:', implode(' | ', $filterDescription) ?: 'Nessuno']);
        $finalCollection->push([]);  // Riga vuota per separare dai dati

        // 2. LOGICA DI RAGGRUPPAMENTO E TOTALI
        if ($this->groupBy) {
            $groups = $rows->groupBy($this->groupBy);

            foreach ($groups as $groupName => $items) {
                // Aggiungiamo le righe del gruppo
                foreach ($items as $item) {
                    $finalCollection->push($item);
                }

                // Inseriamo la riga del Totale per questo gruppo
                $summaryRow = [];
                // Inizializziamo la riga vuota
                foreach ($rows->first() as $key => $val) {
                    $summaryRow[$key] = '';
                }

                // Impostiamo l'etichetta sulla colonna di raggruppamento
                $summaryRow[$this->groupBy] = 'TOTALE ' . strtoupper($groupName);

                // Calcoliamo le somme per le colonne richieste
                foreach ($this->sumColumns as $col) {
                    // Pulizia dei dati (rimozione € e conversione virgola per il calcolo)
                    $summaryRow[$col] = $items->sum(function ($item) use ($col) {
                        $val = $item[$col] ?? 0;
                        if (is_string($val)) {
                            $val = str_replace(['€', '.', ' '], '', $val);
                            $val = (float) str_replace(',', '.', $val);
                        }
                        return $val;
                    });
                }

                $finalCollection->push($summaryRow);
                $finalCollection->push([]);  // Riga vuota tra gruppi
            }
        } else {
            return $finalCollection->concat($rows);
        }

        return $finalCollection;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'italic' => true]],  // Riga filtri in grassetto
        ];
    }

    public function transform_old(Collection $rows): Collection
    {
        if (!$this->groupBy)
            return $rows;

        $newRows = collect();
        // Aggiungiamo i filtri come prima riga informativa
        $newRows->push($this->getAppliedFiltersHeader());
        $newRows->push([]);  // Riga vuota

        $grouped = $rows->groupBy($this->groupBy);

        foreach ($grouped as $groupValue => $items) {
            // Aggiungiamo i dati del gruppo
            foreach ($items as $item) {
                $newRows->push($item);
            }

            // CREIAMO LA RIGA DEI TOTALI PER IL GRUPPO
            $summaryRow = [];
            foreach ($this->sumColumns as $col) {
                $summaryRow[$col] = $items->sum($col);
            }

            // Etichetta del totale
            $summaryRow[$this->groupBy] = 'TOTALE: ' . $groupValue;

            $newRows->push($summaryRow);
            $newRows->push([]);  // Riga vuota di separazione
        }

        return $newRows;
    }

    protected function getAppliedFiltersHeader(): array
    {
        $filters = request()->input('tableFilters', []);
        $cleanFilters = [];

        foreach ($filters as $key => $f) {
            $val = is_array($f) ? implode(', ', array_filter($f)) : $f;
            if ($val)
                $cleanFilters[] = "$key: $val";
        }

        return ['FILTRI APPLICATI: ' . (empty($cleanFilters) ? 'Nessuno' : implode(' | ', $cleanFilters))];
    }
}
