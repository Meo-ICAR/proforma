<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Imports\InvoicesImport;
use App\Models\Invoice;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
// use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    // Aggiunge il sottotitolo
    public function getSubheading(): string|Htmlable|null
    {
        // $record = $this->getRecord();

        return 'Cliccare sulla riga della fattura da riconciliare. Per escludere una fattura dalla riconciliazione perche non contiene provvigioni o anticipi cliccare su Non Enasarco';
    }

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary')
                ->label('Importa Fatture')
                ->modalHeading('Importazione Massiva')
                ->modalDescription('Trascina o seleziona il file')  // Traduce la description
                ->modalSubmitActionLabel('Importa fatture')
                ->successNotificationTitle('Importazione completata')
                ->failureNotificationTitle("Errore durante l'importazione")
                /*
                 * ->uploadField(fn($field) => $field
                 *     ->label('Carica File')
                 *     ->disk('public')  // <--- DENTRO la closure
                 *     ->directory('import-temp')  // <--- DENTRO la closure
                 *     ->visibility('private'))
                 */
                // ->slideOver()
                ->sampleFileExcel(
                    url: url('FATTURE PASSIVE DAL.xlsx'),
                    sampleButtonLabel: 'Scarica esempio del tipo di file excel richiesto',
                )
                ->use(InvoicesImport::class)
                ->validateUsing([
                    'partita_iva' => 'required',
                    'nr_documento' => 'required',
                    'data_documento_fornitore' => 'required',
                    'importo_totale_fornitore' => 'required',
                    'imponibile_iva' => 'required',
                    'importo_iva' => 'required',
                ])

            /*
             * ->validateUsing([
             *     'name' => 'required',
             *     'email' => 'required|email',
             *     'phone' => ['required', 'numeric'],
             * ])
             * ->mutateAfterValidationUsing(
             *     closure: function(array $data): array{
             *         $data['date'] = $data['date']->format('Y-m-d');
             *         return $data;
             *     },
             *     shouldRetainBeforeValidationMutation: true // if this is set, the mutations will be retained after validation (avoids repetition in/of afterValidation)
             * ),
             * // ->uploadField(fn(FileUpload $field) => $field
             * //     ->label('Carica il file Excel delle Fatture')
             * //     ->placeholder('Trascina il file qui o clicca')
             * //     ->required()  // Rende il caricamento obbligatorio
             * // )
             * /*
             * ->beforeImport(function ($data, $action) {  // <--- DEVI AGGIUNGERE $action QUI
             *     // Pulisce la tabella prima di iniziare l'importazione
             *     // 1. Controllo fondamentale: se $data è nullo o non è un array, interrompiamo
             *     if (!is_array($data) || empty($data)) {
             *         Notification::make()
             *             ->title('Errore di lettura')
             *             ->body('Non è stato possibile leggere i dati dal file. Assicurati che il file non sia protetto o danneggiato.')
             *             ->danger()
             *             ->send();
             *
             *         $action->halt();  // Blocca l'esecuzione ed evita il TypeError
             *     }
             *
             *     Invoicein::truncate();
             *     // 1. Definiamo l'elenco esatto delle colonne attese
             *     $expectedColumns = [
             *         'Tipo di documento',
             *         'Nr. documento',
             *         'Nr. Fatt. Acq. Registrata',
             *         'Nr. Nota Cr. Acq. Registrata',
             *         'Data Ricezione Fatt.',
             *         'Codice TD',
             *         'Nr. cliente/fornitore',
             *         'Nome fornitore',
             *         'Partita IVA',
             *         'Nr. Documento Fornitore',
             *         'Allegato',
             *         'Data Documento Fornitore',
             *         'Data Primo Pagamento Prev.',
             *         'Imponibile IVA',
             *         'Importo IVA',
             *         'Importo Totale Fornitore',
             *         'Importo Totale Collegato',
             *         'Data ora Invio/Ricezione',
             *         'Stato',
             *         'ID documento',
             *         'Id SDI',
             *         'Nr. Lotto Documento',
             *         'Nome File Doc. Elettronico',
             *         'Filtro Carichi',
             *         'Cdc Codice',
             *         'Cod. colleg. dimen. 2',
             *         'Allegato in File XML',
             *         'Note 1',
             *         'Note 2',
             *     ];
             *
             *     // 2. Prendiamo la prima riga del file (le intestazioni)
             *     $firstRow = $data[0] ?? null;
             *     dd($firstRow);
             *
             *     if (!$firstRow || !is_array($firstRow)) {
             *         Notification::make()
             *             ->title('File vuoto o aperto altrove')
             *             ->body('Il file Excel caricato non sembra contenere intestazioni o dati.')
             *             ->danger()
             *             ->send();
             *
             *         $action->halt();  // <--- Usa il metodo dell'oggetto action
             *     }
             *
             *     // 3. Confrontiamo le colonne presenti con quelle attese
             *     $missingColumns = [];
             *     $actualColumns = array_keys($firstRow);
             *
             *     foreach ($expectedColumns as $column) {
             *         if (!in_array($column, $actualColumns)) {
             *             $missingColumns[] = $column;
             *         }
             *     }
             *
             *     // 4. Se mancano colonne, blocchiamo tutto e avvisiamo l'utente
             *     if (!empty($missingColumns)) {
             *         Notification::make()
             *             ->title('Struttura Excel non valida')
             *             ->body('Mancano le seguenti colonne: ' . implode(', ', $missingColumns))
             *             ->danger()
             *             ->persistent()  // La notifica rimane finché l'utente non la chiude
             *             ->send();
             *
             *         // Interrompiamo l'esecuzione
             *         $action->halt();  // <--- Usa il metodo dell'oggetto action
             *     }
             * })
             */
            // ->afterImport(function ($data, $livewire) {
            // Perform actions after import
            //   })
            //     ->form([
            //         // Your form fields here
            //    ]),
        ];
    }
}
