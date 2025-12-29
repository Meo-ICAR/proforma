<?php

namespace App\Filament\Resources\Provvigiones\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ProvvigioneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Provvigione')
                    ->tabs([
                        Tabs\Tab::make('Anagrafica')
                            ->schema([
                                TextInput::make('id')
                                    ->label('ID Provvigione')
                                    ->disabled(),
                                TextInput::make('id_pratica')
                                    ->required(),
                                TextInput::make('descrizione'),
                                Select::make('tipo'),
                                TextInput::make('segnalatore'),
                                TextInput::make('istituto_finanziario'),
                                TextInput::make('piva'),
                                TextInput::make('cf'),
                                TextInput::make('fonte'),
                                TextInput::make('tipo_pratica'),
                                TextInput::make('prodotto'),
                                Textarea::make('note')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Importi')
                            ->schema([
                                TextInput::make('importo')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('importo_effettivo')
                                    ->numeric(),
                                TextInput::make('montante')
                                    ->numeric(),
                                TextInput::make('importo_erogato')
                                    ->numeric(),
                                TextInput::make('quota'),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Date')
                            ->schema([
                                DatePicker::make('data_inserimento_compenso')
                                    ->label('Data Inserimento')
                                    ->required(),
                                DatePicker::make('data_pagamento'),
                                DatePicker::make('data_status'),
                                DatePicker::make('data_inserimento_pratica'),
                                DatePicker::make('data_stipula'),
                                DatePicker::make('data_status_pratica'),
                                DateTimePicker::make('sended_at'),
                                DateTimePicker::make('received_at'),
                                DateTimePicker::make('paided_at'),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Documenti')
                            ->schema([
                                TextInput::make('n_fattura')
                                    ->label('Numero Fattura'),
                                DatePicker::make('data_fattura'),
                                TextInput::make('invoice_number')
                                    ->label('Numero Fattura Estera'),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Stato')
                            ->schema([
                                Select::make('stato')
                                    ->options([
                                        'Inserito' => 'Inserito',
                                        'Inviato' => 'Inviato',
                                        'Pagato' => 'Pagato',
                                    ])
                                    ->required(),
                                TextInput::make('status_compenso'),
                                TextInput::make('status_pratica'),
                                TextInput::make('status_pagamento'),
                                TextInput::make('macrostatus'),
                                Toggle::make('annullato'),
                                Toggle::make('coordinamento'),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Altro')
                            ->schema([
                                TextInput::make('legacy_id'),
                                TextInput::make('cognome'),
                                TextInput::make('nome'),
                                TextInput::make('denominazione_riferimento'),
                                TextInput::make('entrata_uscita'),
                                TextInput::make('proforma_id')
                                    ->numeric(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull()
            ]);
    }
}
