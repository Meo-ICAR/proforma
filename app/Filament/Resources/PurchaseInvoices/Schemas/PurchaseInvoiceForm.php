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
                Section::make('Invoice Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('number')
                                    ->label('Invoice Number')
                                    ->required()
                                    ->maxLength(255),
                                DatePicker::make('registration_date')
                                    ->label('Registration Date'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('supplier')
                                    ->label('Supplier Name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                    ]),
                Section::make('Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                               ->label('No Provvigioni')
                                    ->afterStateUpdated(function ($record, $state) {
                                        $record->update(['closed' => $state]);
                                    })
                                    ->default(false)
                                    /*
                                     * ->disabled(fn($record) => $record && $record->proformas()->count() > 0)
                                     */
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
                Section::make('Supplier Details')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('vat_number')
                                    ->label('VAT Number')
                                    ->maxLength(255),
                                TextInput::make('fiscal_code')
                                    ->label('Fiscal Code')
                                    ->maxLength(255),
                                TextInput::make('document_type')
                                    ->label('Document Type')
                                    ->maxLength(255),
                                TextInput::make('location_code')
                                    ->label('Location Code')
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Financial Information')
                    ->collapsed()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('supplier_number')
                                    ->label('Supplier Number')
                                    ->maxLength(255),
                                TextInput::make('amount_including_vat')
                                    ->label('Amount Including VAT')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('residual_amount')
                                    ->label('Residual Amount')
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('currency_code')
                                    ->label('Currency Code')
                                    ->maxLength(3)
                                    ->default('EUR'),
                                TextInput::make('exchange_rate')
                                    ->label('Exchange Rate')
                                    ->numeric()
                                    ->step(0.0001)
                                    ->default(1.0),
                                TextInput::make('supplier_category')
                                    ->label('Supplier Category')
                                    ->maxLength(255),
                            ]),
                    ]),
                Section::make('Dates')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('supplier_invoice_number')
                                    ->label('Supplier Invoice Number')
                                    ->maxLength(255),
                                DatePicker::make('document_date')
                                    ->label('Document Date'),
                                DatePicker::make('due_date')
                                    ->label('Due Date'),
                            ]),
                    ]),
                Section::make('Payment & Address Information')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payment_condition_code')
                                    ->label('Payment Condition Code')
                                    ->maxLength(255),
                                TextInput::make('payment_method_code')
                                    ->label('Payment Method Code')
                                    ->maxLength(255),
                                TextInput::make('pay_to_address')
                                    ->label('Payment Address')
                                    ->maxLength(255),
                                TextInput::make('pay_to_city')
                                    ->label('Payment City')
                                    ->maxLength(255),
                                TextInput::make('pay_to_cap')
                                    ->label('Payment CAP')
                                    ->maxLength(10),
                                TextInput::make('pay_to_country_code')
                                    ->label('Payment Country Code')
                                    ->maxLength(2),
                            ]),
                    ]),
            ]);
    }
}
