<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('competenza')
                    ->required()
                    ->default('2025'),
                TextInput::make('clienti_id'),
                TextInput::make('fornitore_piva'),
                TextInput::make('fornitore'),
                TextInput::make('cliente_piva'),
                TextInput::make('cliente'),
                TextInput::make('invoice_number')
                    ->required(),
                DateTimePicker::make('invoice_date')
                    ->required(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('delta')
                    ->numeric(),
                DateTimePicker::make('sended_at'),
                DateTimePicker::make('sended2_at'),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('importo_iva')
                    ->numeric(),
                TextInput::make('importo_totale_fornitore')
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('EUR'),
                TextInput::make('payment_method'),
                TextInput::make('status')
                    ->required()
                    ->default('imported'),
                DatePicker::make('paid_at'),
                Toggle::make('isreconiled')
                    ->required(),
                Toggle::make('is_notenasarco')
                    ->required(),
                Textarea::make('xml_data')
                    ->columnSpanFull(),
                TextInput::make('coge'),
            ]);
    }
}
