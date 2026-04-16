<?php

namespace App\Filament\Resources\SalesInvoices\Tables;

use App\Filament\Resources\Provvigioni\ProvvigioniResource;
use App\Filament\Resources\SalesInvoices\Pages\CreateSalesInvoice;
use App\Models\Clienti;
use App\Models\Fornitore;
use App\Models\SalesInvoice;
use App\Services\SalesInvoiceCreditNoteImportService;
use App\Services\SalesInvoiceImportService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SalesInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->selectable()
            ->paginated(false)
            ->groups([
                Group::make('customer_name')
                    ->label('Cliente')
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('number')
                    ->label('Numero')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Importo Totale')
                    ->money('EUR')
                    ->summarize([
                        Sum::make()
                            ->money('EUR')
                            ->label('')
                    ])
                    ->sortable(),
                TextColumn::make('vat_number')
                    ->label('Partita IVA')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoiceable_type')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('registration_date')
                    ->label('Data Registrazione')
                    ->date('d/m/Y')
                    ->sortable(),
                IconColumn::make('is_nopractice')
                    ->label('No Provvigioni')
                    ->boolean(),
                TextColumn::make('residual_amount')
                    ->label('Importo Residuo')
                    ->money('EUR')
                    ->sortable()
                    ->color('warning'),
                TextColumn::make('document_type')
                    ->label('Tipo Doc.')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('cancelled')
                    ->label('Annullata')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('Tipo Documento')
                    ->options(SalesInvoice::distinct('document_type')
                        ->whereNotNull('document_type')
                        ->pluck('document_type', 'document_type')
                        ->toArray()),
                Filter::make('registration_date')
                    ->label('Data Registrazione')
                    ->form([
                        DatePicker::make('registered_from')
                            ->label('Da'),
                        DatePicker::make('registered_until')
                            ->label('A'),
                    ])
                    ->query(function (array $data) {
                        return SalesInvoice::query()
                            ->when(
                                $data['registered_from'],
                                fn($query, $date) => $query->whereDate('registration_date', '>=', $date)
                            )
                            ->when(
                                $data['registered_until'],
                                fn($query, $date) => $query->whereDate('registration_date', '<=', $date)
                            );
                    }),
                Filter::make('invoiceable_id')
                    ->label('Non ancora collegato a Cliente / Mandante')
                    ->query(fn($query) => $query->whereNull('invoiceable_id')),
                Filter::make('cancelled')
                    ->label('Annullate')
                    ->query(fn($query) => $query->where('cancelled', true)),
                Filter::make('is_nopractice')
                    ->label('Non Practice')
                    ->query(fn($query) => $query->where('is_nopractice', true)),
            ])
            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
                Action::make('view_practice_commissions')
                    ->label('Abbina Prov.')
                    ->visible(fn($record) => !is_null($record->invoiceable_id) && !($record->is_nopractice) && ($record->invoiceable_type === 'App\Models\Clienti') && ($record->document_type === 'TD04'))
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->url(fn($record) => ProvvigioniResource::getUrl('attive', [
                        'tableFilters' => [
                            'Clienti_id' => [
                                'values' => [
                                    $record->invoiceable_id
                                ]
                            ],
                            'invoice_at' => [
                                'date' => $record->registration_date?->format('Y-m-d')
                            ]
                        ]
                    ]))
                    ->openUrlInNewTab(),
                Action::make('attach_to_model')
                    ->label('Aggiungi')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->visible(fn($record) => is_null($record->invoiceable_id))
                    ->form([
                        Select::make('client_type')
                            ->label('Tipo')
                            ->options([
                                'App\Models\Clienti' => 'Istituti',
                                //    'App\Models\Clienti' => 'Mandanti',
                                // 'App\Models\Agent' => 'Agenti',
                            ])
                            ->default('App\Models\Clienti')
                            ->required()
                            ->reactive(),
                        TextInput::make('client_name')
                            ->label('Nome Record (cerca o crea nuovo)')
                            ->default(fn($record) => $record->customer_name)
                            ->required()
                            ->helperText('Inserisci un nome esistente o uno nuovo per creare automaticamente il record'),
                    ])
                    ->action(function (array $data, $record) {
                        $recordId = null;
                        $searchTerm = $data['client_name'] ?? null;

                        // Se è vuoto o null, crea il record
                        if (is_null($searchTerm) || $searchTerm === '') {
                            $newRecord = match ($data['client_type']) {
                                'App\Models\Clienti' => Clienti::create(['name' => $record->customer_name . date('Y-m-d H:i'),
                                    'vat_number' => $record->vat_number]),

                                /*
                                 * 'App\Models\Client' => Client::create(['name' => $record->customer_name . date('Y-m-d H:i'),
                                 *     'vat_number' => $record->vat_number]),
                                 *
                                 * 'App\Models\Agent' => Agent::create(['name' => $record->customer_name . date('Y-m-d H:i'),
                                 *     'vat_number' => $record->vat_number]),
                                 */
                                default => 'App\Models\Clienti'
                            };

                            if ($newRecord) {
                                $recordId = $newRecord->id;
                            }
                        } else {
                            // Verifica se esiste un record con questo nome
                            $existingRecord = match ($data['client_type']) {
                                'App\Models\Client' => Client::where('name', '=', $searchTerm)->first(['id']),
                                'App\Models\Agent' => Agent::where('name', '=', $searchTerm)->first(['id']),
                                'App\Models\Clienti' => Clienti::where('name', '=', $searchTerm)->first(['id']),
                                default => null
                            };

                            if ($existingRecord) {
                                $recordId = $existingRecord->id;
                            } else {
                                // Crea nuovo record con il nome cercato
                                $newRecord = match ($data['client_type']) {
                                    'App\Models\Client' => Client::create(['name' => $searchTerm]),
                                    'App\Models\Agent' => Agent::create(['name' => $searchTerm]),
                                    'App\Models\Clienti' => Clienti::create(['name' => $searchTerm]),
                                    default => null
                                };

                                if ($newRecord) {
                                    $recordId = $newRecord->id;
                                }
                            }
                        }

                        if ($recordId) {
                            // Prima aggiorna il record corrente
                            $record->update([
                                'invoiceable_type' => $data['client_type'],
                                'invoiceable_id' => $recordId,
                            ]);

                            // Poi associa tutte le altre fatture dello stesso cliente
                            $updatedCount = SalesInvoice::where('customer_name', $record->customer_name)
                                ->whereNull('invoiceable_id')
                                ->update([
                                    'invoiceable_type' => $data['client_type'],
                                    'invoiceable_id' => $recordId,
                                ]);

                            $totalUpdated = $updatedCount + 1;  // +1 per il record corrente

                            $modelType = match ($data['client_type']) {
                                'App\Models\Client' => 'Client',
                                'App\Models\Clienti' => 'Clienti',
                                'App\Models\Agent' => 'Agent',
                                default => 'Record'
                            };

                            $actionText = (is_null($searchTerm) || $searchTerm === '') ? 'creato e associato' : 'associato';
                            Notification::make()
                                ->title('Fattura associata')
                                ->body("{$totalUpdated} fatture del cliente '{$record->customer_name}' {$actionText} correttamente a {$modelType}")
                                ->success()
                                ->send();
                        }
                    }),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    Action::make('bulk_attach_to_model')
                        ->label('Associa')
                        ->icon('heroicon-o-link')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->form([
                            Select::make('client_type')
                                ->label('Tipo')
                                ->options([
                                    'App\Models\Client' => 'Clienti',
                                    //  'App\Models\Agent' => 'Agenti',
                                    'App\Models\Clienti' => 'Mandanti',
                                ])
                                ->default('App\Models\Clienti')
                                ->required()
                                ->reactive(),
                            Select::make('client_id')
                                ->label('Seleziona Record')
                                ->options(function (callable $get) {
                                    $type = $get('client_type');
                                    if (!$type)
                                        return [];

                                    return match ($type) {
                                        'App\Models\Client' => Client::pluck('name', 'id')->sort(),
                                        'App\Models\Agent' => Agent::pluck('name', 'id')->sort(),
                                        'App\Models\Clienti' => Clienti::whereNull('vat_number')->orWhere('vat_number', '=', '')->pluck('name', 'id')->sort(),
                                        default => []
                                    };
                                })
                                ->required()
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search, callable $get) {
                                    $type = $get('client_type');
                                    if (!$type)
                                        return [];

                                    return match ($type) {
                                        'App\Models\Client' => Client::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id'),
                                        'App\Models\Agent' => Agent::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id'),
                                        'App\Models\Clienti' => Clienti::where(function ($query) use ($search) {
                                            $query
                                                ->whereNull('vat_number')
                                                ->orWhere('vat_number', '=', '');
                                        })->where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id'),
                                        default => []
                                    };
                                }),
                        ])
                        ->action(function (array $data, $records) {
                            $totalUpdated = 0;
                            $recordId = null;

                            // Process each record individually
                            foreach ($records as $record) {
                                if (!is_null($record->invoiceable_id)) {
                                    continue;  // Skip if already linked
                                }

                                // Reset recordId for each record
                                $recordId = null;

                                // Se è selezionato "new", crea il record
                                if ($data['client_id'] === 'new') {
                                    $newRecord = match ($data['client_type']) {
                                        'App\Models\Clienti' => Clienti::create(['name' => $record->customer_name,
                                            'vat_number' => $record->vat_number]),

                                        /*
                                         * 'App\Models\Agent' => Agent::create(['name' => $record->customer_name,
                                         *     'vat_number' => $record->vat_number]),
                                         * 'App\Models\Clienti' => Clienti::create(['name' => $record->customer_name,
                                         *     'vat_number' => $record->vat_number]),
                                         */
                                        default => 'App\Models\Clienti'
                                    };

                                    if ($newRecord) {
                                        $recordId = $newRecord->id;
                                    }
                                } else {
                                    // Per i Clienti, cerca prima per VAT number, poi crea se non trovato
                                    if ($data['client_type'] === 'App\Models\Clienti') {
                                        $existingClienti = Clienti::where(function ($query) use ($record) {
                                            $query
                                                ->whereNull('vat_number')
                                                ->orWhere('vat_number', '=', '');
                                        })->where('name', '=', $record->customer_name)->first(['id']);

                                        if ($existingClienti) {
                                            $recordId = $existingClienti->id;
                                        } else {
                                            // Crea nuovo Clienti solo se non esiste
                                            $newClienti = Clienti::create(['name' => $record->customer_name,
                                                'vat_number' => $record->vat_number]);
                                            $recordId = $newClienti->id;
                                        }
                                    } else {
                                        $recordId = $data['client_id'];
                                    }
                                }

                                if ($recordId) {
                                    // Aggiorna il record corrente
                                    $record->update([
                                        'invoiceable_type' => $data['client_type'],
                                        'invoiceable_id' => $recordId,
                                    ]);
                                    $totalUpdated++;

                                    // Associa tutte le altre fatture dello stesso cliente
                                    $additionalUpdated = SalesInvoice::where('customer_name', $record->customer_name)
                                        ->whereNull('invoiceable_id')
                                        ->where('id', '!=', $record->id)  // Escludi il record corrente
                                        ->update([
                                            'invoiceable_type' => $data['client_type'],
                                            'invoiceable_id' => $recordId,
                                        ]);
                                    $totalUpdated += $additionalUpdated;
                                }
                            }

                            $modelType = match ($data['client_type']) {
                                'App\Models\Clienti' => 'Istituti',
                                //                                'App\Models\Agent' => 'Agenti',
                                //   default =>  'App\Models\Clienti'
                            };

                            $actionText = $data['client_id'] === 'new' ? 'creati e associati' : 'associati';
                            Notification::make()
                                ->title('Fatture associate')
                                ->body("{$totalUpdated} fatture {$actionText} correttamente a {$modelType} (incluse tutte quelle degli stessi clienti)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                Action::make('import_sales_invoices')
                    ->label('Importa Note Credito')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->form([
                        FileUpload::make('import_file_excel')
                            ->label('File Excel')
                            ->helperText('Carica un file Excel con i dati delle fatture di vendita')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(10240)  // 10MB
                            ->directory('sales-invoice-imports')
                            ->visibility('private')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = storage_path('app/private/' . $data['import_file_excel']);
                            $companyId = Auth::user()->company_id;
                            $filename = basename($data['import_file_excel']);

                            $importService = new SalesInvoiceCreditNoteImportService($filename);
                            $results = $importService->import($filePath, $companyId);

                            Notification::make()
                                ->title('Importazione Excel completata')
                                ->body("Importazione da {$filename} completata. Importate: {$results['imported']}, Aggiornate: {$results['updated']}, Errori: {$results['errors']}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Errore importazione Excel')
                                ->body('Errore durante importazione: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('import_sales_invoices_excel')
                    ->label('Importa Fatture Vendita Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->form([
                        FileUpload::make('import_file_excel')
                            ->label('File Excel')
                            ->helperText('Carica un file Excel con i dati delle fatture di vendita')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(10240)  // 10MB
                            ->directory('sales-invoice-imports')
                            ->visibility('private')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = storage_path('app/private/' . $data['import_file_excel']);
                            $companyId = Auth::user()->company_id;
                            $filename = basename($data['import_file_excel']);

                            $importService = new SalesInvoiceImportService($filename);
                            $results = $importService->import($filePath, $companyId);

                            Notification::make()
                                ->title('Importazione Excel completata')
                                ->body("Importazione da {$filename} completata. Importate: {$results['imported']}, Aggiornate: {$results['updated']}, Errori: {$results['errors']}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Errore importazione Excel')
                                ->body('Errore durante importazione: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('associate_sales_invoices')
                    ->label('Abbina')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->action(function () {
                        try {
                            $companyId = Auth::user()->company_id;
                            $importService = new SalesInvoiceCreditNoteImportService();
                            $importService->setCompanyId($companyId);  // Usa il metodo setter

                            // Esegui solo le funzioni di matching per sales invoices
                            $importService->matchClientisByVatNumber();
                            //  $importService->matchClientsByVatNumber();

                            Notification::make()
                                ->title('Associazione completata')
                                ->body('Le fatture di vendita sono state associate a mandanti e clienti')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Errore associazione')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->emptyStateActions([
                //  CreateAction::make(),
            ])
            ->defaultSort('registration_date', 'desc');
    }
}
