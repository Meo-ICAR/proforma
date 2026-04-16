<?php

namespace App\Filament\Resources\PurchaseInvoices\Tables;

use App\Models\Clienti;
use App\Models\Fornitore;
use App\Models\PurchaseInvoice;
use App\Services\PurchaseCreditNoteImportService;
use App\Services\PurchaseInvoiceImportService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PurchaseInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(['all', 10, 25, 50, 100])
            ->reorderableColumns()
            ->groups([
                Group::make('supplier')
                    ->label('Fornitore')
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('number')
                    ->label('Doc. n.')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('document_date')
                    ->label('Del')
                    ->date()
                    ->sortable(),
                TextColumn::make('supplier')
                    ->label('Fornitore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vat_number')
                    ->label('Partita IVA')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('EUR')
                    ->sortable()
                    ->summarize(Sum::make()->money('EUR')->label('')),
                TextColumn::make('amount_including_vat')
                    ->label('Amount incl. VAT')
                    ->money('EUR')
                    ->sortable()
                    ->summarize(Sum::make()->money('EUR')->label('')),
                TextColumn::make('residual_amount')
                    ->label('Residual')
                    ->money('EUR')
                    ->sortable()
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->color(function ($state) {
                        return $state > 0 ? 'warning' : 'success';
                    }),
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->color(function ($state, $record) {
                        if (!$state || $record->closed)
                            return null;
                        return $state->isPast() ? 'danger' : null;
                    }),
                IconColumn::make('closed')
                    ->label('Closed')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('cancelled')
                    ->label('Cancelled')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('invoiceable_type')
                    ->label('Attached To')
                    ->options([
                        null => 'Nessuno',
                        'App\Models\Clienti' => 'Clienti',
                        'App\Models\Fornitore' => 'Fornitore',
                    ]),
                Filter::make('invoiceable_id')
                    ->label('Non ancora collegato a Clienti / Fornitore')
                    ->query(fn($query) => $query->whereNull('invoiceable_id')),
                Filter::make('open_invoices')
                    ->label('Open Invoices')
                    ->query(fn($query) => $query->where('closed', false)),
                Filter::make('overdue')
                    ->label('Overdue')
                    ->query(function ($query) {
                        return $query
                            ->where('closed', false)
                            ->whereNotNull('due_date')
                            ->where('due_date', '<', now());
                    }),
                Filter::make('is_nopractice')
                    ->label('Non Practice')
                    ->query(fn($query) => $query->where('is_nopractice', true)),
            ])
            ->recordActions([
                //   EditAction::make(),
                Action::make('attach_to_model')
                    ->label('Aggiungi')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->visible(fn($record) => is_null($record->invoiceable_id))
                    ->form([
                        Select::make('invoiceable_type')
                            ->label('Tipo')
                            ->options([
                                'App\Models\Clienti' => 'Consulenti',
                                'App\Models\Fornitore' => 'Fornitorei',
                                //  'App\Models\Principal' => 'Principal',
                            ])
                            ->default('App\Models\Clienti')
                            ->required()
                            ->reactive(),
                        TextInput::make('invoiceable_name')
                            ->label('Nome Record (cerca o crea nuovo)')
                            ->default(fn($record) => $record->supplier)
                            ->required()
                            ->helperText('Inserisci un nome esistente o uno nuovo per creare automaticamente il record'),
                    ])
                    ->action(function (array $data, $record) {
                        $invoiceableId = null;
                        $searchTerm = $data['invoiceable_name'] ?? null;

                        // Se è vuoto o null, crea il record
                        if (is_null($searchTerm) || $searchTerm === '') {
                            $newRecord = match ($data['invoiceable_type']) {
                                'App\Models\Clienti' => Clienti::create(['name' => $record->supplier . date('Y-m-d H:i'),
                                    'vat_number' => $record->vat_number]),
                                'App\Models\Fornitore' => Fornitore::create(['name' => $record->supplier . date('Y-m-d H:i'),
                                    'vat_number' => $record->vat_number]),
                                'App\Models\Principal' => Principal::create(['name' => $record->supplier . date('Y-m-d H:i'),
                                    'vat_number' => $record->vat_number]),
                                default => null
                            };

                            if ($newRecord) {
                                $invoiceableId = $newRecord->id;
                            }
                        } else {
                            // Verifica se esiste un record con questo nome
                            $existingRecord = match ($data['invoiceable_type']) {
                                'App\Models\Clienti' => Clienti::where('name', $searchTerm)->first(),
                                'App\Models\Fornitore' => Fornitore::where('name', $searchTerm)->first(),
                                'App\Models\Principal' => Principal::where('name', $searchTerm)->first(),
                                default => null
                            };

                            if ($existingRecord) {
                                $invoiceableId = $existingRecord->id;
                            } else {
                                // Crea nuovo record con il nome cercato
                                $newRecord = match ($data['invoiceable_type']) {
                                    'App\Models\Clienti' => Clienti::create(['name' => $searchTerm,
                                        'vat_number' => $record->vat_number]),
                                    'App\Models\Fornitore' => Fornitore::create(['name' => $searchTerm,
                                        'vat_number' => $record->vat_number]),
                                    'App\Models\Principal' => Principal::create(['name' => $searchTerm,
                                        'vat_number' => $record->vat_number]),
                                    default => null
                                };

                                if ($newRecord) {
                                    $invoiceableId = $newRecord->id;
                                }
                            }
                        }

                        if ($invoiceableId) {
                            // Prima aggiorna il record corrente
                            $record->update([
                                'invoiceable_type' => $data['invoiceable_type'],
                                'invoiceable_id' => $invoiceableId,
                            ]);

                            // Poi associa tutte le altre fatture dello stesso supplier senza attach
                            $updatedCount = PurchaseInvoice::where('supplier', $record->supplier)
                                ->whereNull('invoiceable_id')
                                ->update([
                                    'invoiceable_type' => $data['invoiceable_type'],
                                    'invoiceable_id' => $invoiceableId,
                                ]);

                            $totalUpdated = $updatedCount + 1;  // +1 per il record corrente

                            $actionText = (is_null($searchTerm) || $searchTerm === '') ? 'creato e associato' : 'associato';
                            Notification::make()
                                ->title('Fatture associate')
                                ->body("{$totalUpdated} fatture del supplier '{$record->supplier}' {$actionText} correttamente")
                                ->success()
                                ->send();
                        }
                    }),
            ], position: RecordActionsPosition::BeforeColumns)
            ->headerActions([
                Action::make('import_credit_notes')
                    ->label('Importa Note Credito')
                    ->icon('heroicon-o-document-minus')
                    ->color('warning')
                    ->form([
                        TextInput::make('filename')
                            ->label('Nome File Excel')
                            ->default('Note credito acquisto registrate.xlsx')
                            ->helperText('Inserisci il nome del file Excel da importare dalla cartella public/'),
                    ])
                    ->action(function (array $data) {
                        try {
                            $importService = new PurchaseCreditNoteImportService();
                            $filePath = 'public/' . $data['filename'];

                            if (!file_exists($filePath)) {
                                throw new \Exception("File non trovato: {$filePath}");
                            }

                            $result = $importService->import($filePath, Auth::user()->company_id);

                            Notification::make()
                                ->title('Importazione Note Credito')
                                ->body("Importate: {$result['imported']}, Aggiornate: {$result['updated']}, Errori: {$result['errors']}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Errore Importazione Note Credito')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('import_purchase_invoices_excel')
                    ->label('Importa Fatture Acquisto Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->form([
                        FileUpload::make('import_file_excel')
                            ->label('File Excel')
                            ->helperText('Carica un file Excel con i dati delle fatture di acquisto')
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(10240)  // 10MB
                            ->directory('purchase-invoice-imports')
                            ->visibility('private')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            $filePath = storage_path('app/public/' . $data['import_file_excel']);
                            $companyId = Auth::user()->company_id;
                            $filename = basename($data['import_file_excel']);

                            $importService = new \App\Services\PurchaseInvoiceImportService($filename);
                            $results = $importService->import('public/' . $data['import_file_excel'], $companyId);

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
                Action::make('associate_invoices')
                    ->label('Associa')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->action(function () {
                        try {
                            $importService = new PurchaseInvoiceImportService();

                            // Esegui solo le funzioni di matching
                            //  $importService->matchFornitoresByVatNumber(Auth::user()->company_id);
                            $importService->matchClientisByVatNumber(Auth::user()->company_id);

                            Notification::make()
                                ->title('Associazione completata')
                                ->body('Le fatture sono state associate a Fornitorei e Clientii')
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
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_as_closed')
                        ->label('Chiudi Selezionati')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $count = $records->where('closed', false)->count();
                            $records->where('closed', false)->each->update(['closed' => true]);

                            Notification::make()
                                ->title('Fatture chiuse')
                                ->body("{$count} fatture chiuse correttamente")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Action::make('bulk_attach_to_model')
                        ->label('Associa Fornitoree/Consulente Selezionato')
                        ->icon('heroicon-o-link')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->form([
                            Select::make('invoiceable_type')
                                ->label('Tipo')
                                ->options([
                                    'App\Models\Clienti' => 'Consulenti',
                                    'App\Models\Fornitore' => 'Fornitorei',
                                    // 'App\Models\Principal' => 'Mandanti',
                                ])
                                ->default('App\Models\Fornitore')
                                ->required()
                                ->reactive(),
                            Select::make('invoiceable_id')
                                ->label('Seleziona Record')
                                ->options(function (callable $get) {
                                    $type = $get('invoiceable_type');
                                    if (!$type)
                                        return [];

                                    return match ($type) {
                                        'App\Models\Clienti' => Clienti::pluck('name', 'id')->where('is_company', true)->sort(),
                                        'App\Models\Fornitore' => Fornitore::pluck('name', 'id')->sort(),
                                        'App\Models\Principal' => Principal::pluck('name', 'id')->sort(),
                                        default => []
                                    };
                                })
                                ->required()
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search, callable $get) {
                                    $type = $get('invoiceable_type');
                                    if (!$type)
                                        return [];

                                    return match ($type) {
                                        'App\Models\Clienti' => Clienti::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id'),
                                        'App\Models\Fornitore' => Fornitore::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id'),
                                        'App\Models\Principal' => Principal::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id'),
                                        default => []
                                    };
                                }),
                        ])
                        ->action(function (array $data, $records) {
                            $totalUpdated = 0;
                            $invoiceableId = null;

                            // Se è selezionato "new", crea il record
                            if ($data['invoiceable_id'] === 'new') {
                                $newRecord = match ($data['invoiceable_type']) {
                                    'App\Models\Clienti' => Clienti::create(['name' => 'Nuovo Clienti ' . date('Y-m-d H:i')]),
                                    'App\Models\Fornitore' => Fornitore::create(['name' => 'Nuovo Fornitore ' . date('Y-m-d H:i')]),
                                    'App\Models\Principal' => Principal::create(['name' => 'Nuovo Principal ' . date('Y-m-d H:i')]),
                                    default => null
                                };

                                if ($newRecord) {
                                    $invoiceableId = $newRecord->id;
                                }
                            } else {
                                $invoiceableId = $data['invoiceable_id'];
                            }

                            if ($invoiceableId) {
                                foreach ($records as $record) {
                                    if (is_null($record->invoiceable_id)) {
                                        // Aggiorna il record corrente
                                        $record->update([
                                            'invoiceable_type' => $data['invoiceable_type'],
                                            'invoiceable_id' => $invoiceableId,
                                        ]);
                                        $totalUpdated++;

                                        // Associa tutte le altre fatture dello stesso supplier
                                        $additionalUpdated = PurchaseInvoice::where('supplier', $record->supplier)
                                            ->whereNull('invoiceable_id')
                                            ->where('id', '!=', $record->id)  // Escludi il record corrente
                                            ->update([
                                                'invoiceable_type' => $data['invoiceable_type'],
                                                'invoiceable_id' => $invoiceableId,
                                            ]);
                                        $totalUpdated += $additionalUpdated;
                                    }
                                }

                                $actionText = $data['invoiceable_id'] === 'new' ? 'creati e associati' : 'associati';
                                Notification::make()
                                    ->title('Fatture associate')
                                    ->body("{$totalUpdated} fatture {$actionText} correttamente (incluse tutte quelle degli stessi supplier)")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('document_date', 'desc');
    }
}
