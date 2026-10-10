<?php

namespace App\Filament\Resources\InvoiceIns\Pages;

use App\Filament\Resources\InvoiceIns\InvoiceInResource;
use App\Imports\InvoiceinsImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ListInvoiceIns extends ListRecords
{
    protected static string $resource = InvoiceInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importaFatture')
                ->color('primary')
                ->label('Importa Fatture')
                ->modalHeading('Importazione Massiva')
                ->modalDescription('Trascina o seleziona il file')
                ->modalSubmitActionLabel('Carica file')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel delle fatture')
                        ->required()
                        ->disk('local')
                        ->directory('import-temp')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ]),
                ])
                ->action(function (array $data): void {
                    $path = $data['file'];

                    try {
                        Excel::import(new InvoiceinsImport, $path, 'local');
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title("Errore durante l'importazione")
                            ->body(class_basename($e))
                            ->danger()
                            ->send();

                        return;
                    } finally {
                        Storage::disk('local')->delete($path);
                    }

                    Notification::make()->title('Importazione completata')->success()->send();
                }),
        ];
    }
}
