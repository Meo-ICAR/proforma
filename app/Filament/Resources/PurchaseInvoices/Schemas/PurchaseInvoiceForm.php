<?php

namespace App\Filament\Resources\PurchaseInvoices\Schemas;

use App\Filament\Resources\PurchaseInvoices\RelationManagers\ProformasAfterRegistrationRelationManager;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Company;
use App\Models\Principal;
use App\Services\PurchaseInvoiceImportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PurchaseInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informazioni fattura')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('number')
                                    ->label('Numero fattura')
                                    ->required()
                                    ->maxLength(255),
                                DatePicker::make('registration_date')
                                    ->label('Data registrazione'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('supplier')
                                    ->label('Fornitore')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('amount')
                                    ->label('Importo')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                    ]),
                Section::make('Stato')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_nopractice')
                                    ->label('No Provvigioni')
                                    ->afterStateUpdated(function ($record, $state) {
                                        $record->update(['closed' => $state]);
                                    })
                                    ->default(false)
                                    ->helperText('Fattura non relativa a finanziamenti'),
                                Toggle::make('closed')
                                    ->label('Riconciliata')
                                    ->default(false),
                                Toggle::make('cancelled')
                                    ->label('Annullata')
                                    ->default(false),
                                Toggle::make('corrected')
                                    ->label('Corretta')
                                    ->default(false),
                            ]),
                    ]),
                Section::make('Dati fornitore')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('vat_number')
                                    ->label('Partita IVA')
                                    ->maxLength(255),
                                TextInput::make('fiscal_code')
                                    ->label('Codice fiscale')
                                    ->maxLength(255),
                                TextInput::make('document_type')
                                    ->label('Tipo documento')
                                    ->maxLength(255),
                                TextInput::make('location_code')
                                    ->label('Codice ubicazione')
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Dati finanziari')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('supplier_number')
                                    ->label('Numero fornitore')
                                    ->maxLength(255),
                                TextInput::make('amount_including_vat')
                                    ->label('Importo IVA inclusa')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('residual_amount')
                                    ->label('Importo residuo')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('currency_code')
                                    ->label('Codice valuta')
                                    ->maxLength(3)
                                    ->default('EUR'),
                                TextInput::make('exchange_rate')
                                    ->label('Tasso di cambio')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->default(1.0),
                                TextInput::make('supplier_category')
                                    ->label('Categoria fornitore')
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Date')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('supplier_invoice_number')
                                    ->label('Numero fattura fornitore')
                                    ->maxLength(255),
                                DatePicker::make('document_date')
                                    ->label('Data documento'),
                                DatePicker::make('due_date')
                                    ->label('Data scadenza'),
                            ]),
                    ]),
                Section::make('Pagamento e indirizzo')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payment_condition_code')
                                    ->label('Codice condizione pagamento')
                                    ->maxLength(255),
                                TextInput::make('payment_method_code')
                                    ->label('Codice metodo pagamento')
                                    ->maxLength(255),
                                TextInput::make('pay_to_address')
                                    ->label('Indirizzo pagamento')
                                    ->maxLength(255),
                                TextInput::make('pay_to_city')
                                    ->label('Città pagamento')
                                    ->maxLength(255),
                                TextInput::make('pay_to_cap')
                                    ->label('CAP pagamento')
                                    ->maxLength(10),
                                TextInput::make('pay_to_country_code')
                                    ->label('Codice paese pagamento')
                                    ->maxLength(2),
                            ]),
                    ]),
            ]);
    }
}
