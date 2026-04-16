<?php

namespace App\Filament\Resources\SalesInvoices\Tables;

use App\Filament\Resources\Provvigioni\ProvvigioniResource;
use App\Filament\Resources\SalesInvoices\Pages\CreateSalesInvoice;
use App\Models\Client;
use App\Models\Clienti;
use App\Models\SalesInvoice;
use App\Services\SalesInvoiceCreditNoteImportService;
use App\Services\SalesInvoiceImportService;
use App\Services\SalesInvoiceMatchingService;
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
            ->reorderableColumns()
            ->selectable()
            ->paginated(['all', 10, 25, 50, 100])
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
                            $matchService = new SalesInvoiceMatchingService();
                            $matchService->setCompanyId($companyId);  // Usa il metodo setter

                            // Esegui solo le funzioni di matching per sales invoices
                            $matchService->matchClientisByVatNumber();
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
            ->recordActions([
                Action::make('attach_to_model')
                    ->visible(fn($record) => is_null($record->invoiceable_id))
                    ->label('Associa')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->form(function ($record) {
                        if (empty($record->vat_number)) {
                            // No VAT number - show Client options
                            return [
                                Select::make('client_id')
                                    ->label('Cliente')
                                    ->options(Client::pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                            ];
                        } else {
                            // Has VAT number - show Clienti options
                            return [
                                Select::make('clienti_id')
                                    ->label('Cliente')
                                    ->options(Clienti::pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                            ];
                        }
                    })
                    ->action(function ($record, $data) {
                        try {
                            if (empty($record->vat_number)) {
                                // Attach to Client
                                $record->update([
                                    'invoiceable_type' => 'App\Models\Client',
                                    'invoiceable_id' => $data['client_id']
                                ]);
                            } else {
                                // Attach to Clienti
                                $record->update([
                                    'invoiceable_type' => 'App\Models\Clienti',
                                    'invoiceable_id' => $data['clienti_id']
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
            ->emptyStateActions([
                //  CreateAction::make(),
            ])
            ->defaultSort('registration_date', 'desc');
    }
}
