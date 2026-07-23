<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuickExcelExportAction
{
    public static function make(?string $name = 'quick_excel_export'): Action
    {
        return Action::make($name)
            ->label('Backup')
            ->icon('heroicon-m-circle-stack')
            ->color('success')
            ->action(function (Action $action): StreamedResponse {
                /** @var Builder $query */
                // Recupera la query con i filtri applicati
                $query = $action->getLivewire()->getFilteredTableQuery();

                $model = $query->getModel();
                $tableName = $model->getTable();
                $fileName = "{$tableName}-export-".now()->format('Y-m-d_H-i').'.xlsx';

                return response()->streamDownload(function () use ($query) {
                    $spreadsheet = new Spreadsheet;
                    $sheet = $spreadsheet->getActiveSheet();

                    $rowIndex = 1;
                    $isHeaderWritten = false;

                    // cursor() legge 1 riga alla volta dal DB per non saturare la RAM
                    foreach ($query->cursor() as $row) {
                        $array = $row->toArray();

                        // Pulizia/conversione campi complessi (es. array o JSON) in stringa per Excel
                        $dataValues = array_map(function ($value) {
                            if (is_array($value) || is_object($value)) {
                                return json_encode($value, JSON_UNESCAPED_UNICODE);
                            }

                            return $value;
                        }, array_values($array));

                        // Intestazione con i nomi delle colonne
                        if (! $isHeaderWritten) {
                            $headers = array_keys($array);
                            $sheet->fromArray($headers, null, "A{$rowIndex}");

                            // Stile intestazione: Grassetto
                            $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);

                            $rowIndex++;
                            $isHeaderWritten = true;
                        }

                        // Scrittura riga dati
                        $sheet->fromArray($dataValues, null, "A{$rowIndex}");
                        $rowIndex++;
                    }

                    // Scrive l'Excel sullo stream di output
                    $writer = new Xlsx($spreadsheet);
                    $writer->save('php://output');
                }, $fileName, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control' => 'max-age=0',
                ]);
            });
    }
}
