<?php

namespace App\Filament\Resources\Provvigiones\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProvvigioneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('data_inserimento_compenso'),
                TextInput::make('descrizione'),
                TextInput::make('tipo'),
                TextInput::make('importo')
                    ->numeric(),
                TextInput::make('importo_effettivo')
                    ->numeric(),
                TextInput::make('status_compenso'),
                DatePicker::make('data_pagamento'),
                TextInput::make('n_fattura'),
                DatePicker::make('data_fattura'),
                DatePicker::make('data_status'),
                TextInput::make('denominazione_riferimento'),
                TextInput::make('entrata_uscita'),
                TextInput::make('id_pratica')
                    ->required(),
                TextInput::make('segnalatore'),
                TextInput::make('istituto_finanziario'),
                TextInput::make('piva'),
                TextInput::make('cf'),
                Toggle::make('annullato'),
                Toggle::make('coordinamento'),
                TextInput::make('stato'),
                TextInput::make('proforma_id')
                    ->numeric(),
                TextInput::make('legacy_id'),
                TextInput::make('invoice_number'),
                TextInput::make('cognome'),
                TextInput::make('quota'),
                TextInput::make('nome'),
                TextInput::make('fonte'),
                TextInput::make('tipo_pratica'),
                DatePicker::make('data_inserimento_pratica'),
                DatePicker::make('data_stipula'),
                TextInput::make('prodotto'),
                TextInput::make('macrostatus'),
                TextInput::make('status_pratica'),
                TextInput::make('status_pagamento'),
                DatePicker::make('data_status_pratica'),
                TextInput::make('montante')
                    ->numeric(),
                TextInput::make('importo_erogato')
                    ->numeric(),
                DateTimePicker::make('sended_at'),
                DateTimePicker::make('received_at'),
                DateTimePicker::make('paided_at'),
            ]);
    }
}
