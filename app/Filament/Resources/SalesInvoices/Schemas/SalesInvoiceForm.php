<?php

namespace App\Filament\Resources\SalesInvoices\Schemas;

use App\Models\Client;
use App\Models\Clienti;
use App\Models\Company;
use App\Models\Principal;
use App\Services\SalesInvoiceCreditNoteImportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Repeater;
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
                                TextInput::make('number')
                                    ->label('Numero Fattura')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('invoice_number')
                                    ->label('Numero Fattura Clientie')
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('customer_name')
                                    ->label('Nome Clientie')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('customer_number')
                                    ->label('Numero Clientie')
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Dettagli Clientie')
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
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Importo')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
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
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('document_date')
                                    ->label('Data Documento'),
                                DatePicker::make('registration_date')
                                    ->label('Data Registrazione'),
                                DatePicker::make('due_date')
                                    ->label('Data Scadenza'),
                            ]),
                    ]),
                Section::make('Informazioni Pagamento e Indirizzo')
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
                Section::make('Relazioni')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('invoiceable_type')
                                    ->label('Collega A')
                                    ->options([
                                        Clienti::class => 'Clienti',
                                        Client::class => 'Istituti',
                                        // Client::class => 'Cliente', // Commentato per sales invoices
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('invoiceable_id', null)),
                                Select::make('invoiceable_id')
                                    ->label('Seleziona Record')
                                    ->required()
                                    ->reactive()
                                    ->getSearchResultsUsing(function (callable $get) {
                                        $type = $get('invoiceable_type');
                                        if (!$type)
                                            return [];

                                        $search = request()->get('search');
                                        switch ($type) {
                                            case Clienti::class:
                                                return Clienti::where('name', 'like', "%{$search}%")
                                                    ->orWhere('first_name', 'like', "%{$search}%")
                                                    ->limit(50)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            case Client::class:
                                                returnClient::where('name', 'like', "%{$search}%")
                                                    ->limit(50)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            default:
                                                return [];
                                        }
                                    })
                                    ->getOptionLabelUsing(function (callable $get) {
                                        $type = $get('invoiceable_type');
                                        $id = $get('invoiceable_id');
                                        if (!$type || !$id)
                                            return null;

                                        switch ($type) {
                                            case Clienti::class:
                                                return Clienti::find($id)?->name;
                                            case Client::class:
                                                returnClient::find($id)?->name;
                                            default:
                                                return null;
                                        }
                                    }),
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
                                    ->label('Non relativo ad un finanziamento')
                                    ->default(false)
                                    ->helperText('Seleziona se questa fattura non è associata a una provvigione'),
                            ]),
                    ]),
            ]);
    }
}
