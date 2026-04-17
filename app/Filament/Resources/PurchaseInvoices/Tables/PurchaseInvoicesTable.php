<?php

namespace App\Filament\Resources\PurchaseInvoices\Tables;

use App\Models\Client;
use App\Models\Fornitore;
use App\Models\PurchaseInvoice;
use App\Services\PurchaseCreditNoteImportService;
use App\Services\PurchaseInvoiceImportService;
use App\Services\PurchaseInvoiceMatchingService;
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
                Action::make('attach_to_model')
                    ->visible(fn($record) => is_null($record->invoiceable_id))
                    ->label('Associa')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->form(function ($record) {
                        return [
                            Select::make('action_type')
                                ->label('Azione')
                                ->options([
                                    'create_new' => 'Crea nuovo Consulente',
                                    'select_existing' => 'Seleziona Consulente esistente',
                                    'attach_existing_agent' => 'Seleziona Agente esistente',
                                    'create_agent' => 'Crea nuovo Agente'
                                ])
                                ->default('create_new')
                                ->live()
                                ->reactive(),
                            Select::make('client_id')
                                ->label('Consulente')
                                ->options(Client::orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->visible(fn($get) => $get('action_type') === 'select_existing'),
                            Select::make('agent_id')
                                ->label('Agente')
                                ->options(Fornitore::orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->visible(fn($get) => $get('action_type') === 'attach_existing_agent')
                        ];
                    })
                    ->action(function ($record, $data) {
                        try {
                            if ($data['action_type'] === 'create_new') {
                                // Create new Client
                                $client = Client::create([
                                    'name' => $record->supplier,
                                    'vat_number' => $record->vat_number,
                                    'is_company' => 1,
                                    'is_lead' => 0,
                                    'is_person' => 0,
                                    'is_client' => 0,
                                    'company_id' => Auth::user()->company_id
                                ]);
                                $record->update([
                                    'invoiceable_type' => 'App\Models\Client',
                                    'invoiceable_id' => $client->id
                                ]);
                            } elseif ($data['action_type'] === 'select_existing') {
                                // Attach to existing Client
                                $record->update([
                                    'invoiceable_type' => 'App\Models\Client',
                                    'invoiceable_id' => $data['client_id']
                                ]);
                            } elseif ($data['action_type'] === 'create_agent') {
                                // Create new Agent
                                $agent = Fornitore::create([
                                    'name' => $record->supplier,
                                    'piva' => $record->vat_number,
                                    'is_active' => 1,
                                    'company_id' => Auth::user()->company_id
                                ]);
                                $record->update([
                                    'invoiceable_type' => 'App\Models\Fornitore',
                                    'invoiceable_id' => $agent->id
                                ]);
                            } elseif ($data['action_type'] === 'attach_existing_agent') {
                                // Attach to existing Agent
                                $record->update([
                                    'invoiceable_type' => 'App\Models\Fornitore',
                                    'invoiceable_id' => $data['agent_id']
                                ]);
                                Fornitore::updateOrCreate([
                                    'id' => $data['agent_id']
                                ], [
                                    'name' => $record->supplier,
                                    'piva' => $record->vat_number,
                                ]);
                            }
                            Notification::make()
                                ->title('Associazione completata')
                                ->body('Fattura associata con successo')
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
                Action::make('associate_purchase_invoices')
                    ->label('Abbina')
                    ->icon('heroicon-o-link')
                    ->color('warning')
                    ->action(function () {
                        try {
                            $companyId = Auth::user()->company_id;
                            $matchService = new PurchaseInvoiceMatchingService();
                            $matchService->setCompanyId($companyId);  // Usa il metodo setter

                            // Esegui solo le funzioni di matching per purchase invoices
                            $matchService->matchFornitoresByVatNumber();
                            //  $importService->matchClientsByVatNumber();

                            Notification::make()
                                ->title('Associazione completata')
                                ->body('Le fatture di acquisto sono state associate a consulenti e agenti')
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
                ]),
            ])
            ->defaultSort('document_date', 'desc');
    }
}
