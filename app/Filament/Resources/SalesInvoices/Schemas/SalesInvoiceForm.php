<?php

namespace App\Filament\Resources\SalesInvoices\Schemas;

use App\Models\Client;
use App\Models\Clienti;
use App\Models\Company;
use App\Models\Principal;
use App\Models\Provvigione;
use App\Services\SalesInvoiceCreditNoteImportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class SalesInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informazioni Fattura')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('customer_name')
                                    ->label('Nome Clientie')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('amount')
                                    ->label('Importo')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('registration_date')
                                    ->label('Data Registrazione'),
                                TextInput::make('number')
                                    ->label('Numero Fattura')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Stato')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('closed')
                                    ->label('Chiusa')
                                    ->default(false),
                                Toggle::make('cancelled')
                                    ->label('Annullata')
                                    ->default(false),
                                Toggle::make('corrected')
                                    ->label('Corretta')
                                    ->default(false),
                                Toggle::make('is_nopractice')
                                    ->label('Non relativo a finanziamenti')
                                    ->default(false)
                                    ->helperText('Seleziona se questa fattura non è associata a provvigioni'),
                            ]),
                    ]),
                Section::make('Dettagli Clientie')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('vat_number')
                                    ->label('Partita IVA')
                                    ->maxLength(255),
                                TextInput::make('fiscal_code')
                                    ->label('Codice Fiscale')
                                    ->maxLength(255),
                                TextInput::make('document_type')
                                    ->label('Tipo Documento')
                                    ->maxLength(255),
                                TextInput::make('location_code')
                                    ->label('Codice Luogo')
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Informazioni Finanziarie')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount_including_vat')
                                    ->label('Importo IVA Inclusa')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('residual_amount')
                                    ->label('Importo Residuo')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('currency_code')
                                    ->label('Codice Valuta')
                                    ->maxLength(3)
                                    ->default('EUR'),
                                TextInput::make('exchange_rate')
                                    ->label('Tasso di Cambio')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->default(1.0),
                                TextInput::make('customer_category')
                                    ->label('Categoria Clientie')
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Date')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('document_date')
                                    ->label('Data Documento'),
                                DatePicker::make('due_date')
                                    ->label('Data Scadenza'),
                                TextInput::make('customer_number')
                                    ->label('Numero Clientie')
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Informazioni Pagamento e Indirizzo')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payment_condition_code')
                                    ->label('Codice Condizione Pagamento')
                                    ->maxLength(255),
                                TextInput::make('payment_method_code')
                                    ->label('Codice Metodo Pagamento')
                                    ->maxLength(255),
                                TextInput::make('pay_to_address')
                                    ->label('Indirizzo Pagamento')
                                    ->maxLength(255),
                                TextInput::make('pay_to_city')
                                    ->label('Città Pagamento')
                                    ->maxLength(255),
                                TextInput::make('pay_to_cap')
                                    ->label('CAP Pagamento')
                                    ->maxLength(10),
                                TextInput::make('pay_to_country_code')
                                    ->label('Codice Nazione Pagamento')
                                    ->maxLength(2),
                            ]),
                    ]),
            ]);
    }
}
