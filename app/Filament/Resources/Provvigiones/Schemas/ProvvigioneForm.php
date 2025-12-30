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
                                    ->disabled()
                                    ->required(),
                                TextInput::make('descrizione')
                                    ->disabled(),
                                Select::make('tipo')
                                    ->disabled(),
                                TextInput::make('segnalatore')
                                    ->disabled(),
                                TextInput::make('istituto_finanziario')
                                    ->disabled(),
                                TextInput::make('piva')->disabled(),
                                TextInput::make('cf')->disabled(),
                                TextInput::make('fonte')->disabled(),
                                TextInput::make('tipo_pratica')->disabled(),
                                TextInput::make('prodotto')->disabled(),
                                Textarea::make('note')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Importi')
                            ->schema([
                                TextInput::make('importo')
                                    ->disabled()
                                    ->numeric()
                                    ->required(),
                                TextInput::make('quota'),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Date')
                            ->schema([
                                DatePicker::make('data_inserimento_compenso')
                                    ->label('Data Inserimento')
                                    ->disabled()
                                    ->required(),
                                DatePicker::make('data_pagamento'),
                                DatePicker::make('data_status'),
                                DatePicker::make('data_inserimento_pratica')->disabled(),
                                DatePicker::make('data_stipula'),
                                DatePicker::make('data_status_pratica'),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Documenti')
                            ->schema([
                                TextInput::make('n_fattura')
                                    ->disabled()
                                    ->label('Numero Fattura'),
                                DatePicker::make('data_fattura')->disabled(),
                                TextInput::make('invoice_number')
                                    ->disabled()
                                    ->label('Numero Fattura'),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Stato')
                            ->schema([
                                Select::make('stato')
                                    ->options([
                                        '' => '',
                                        'Inserito' => 'Inserito',
                                        'Sospeso' => 'Sospeso',
                                        'Proforma' => 'Proforma',
                                        'Annullato' => 'Annullato',
                                        'Pagato' => 'Pagato',
                                    ])
                                    ->required(),
                                TextInput::make('status_compenso')->disabled(),
                                TextInput::make('status_pratica')->disabled(),
                                TextInput::make('status_pagamento')->disabled(),
                                TextInput::make('macrostatus')->disabled(),
                                Toggle::make('annullato'),
                                Toggle::make('coordinamento'),
                            ])
                            ->columns(2),
                        Tabs\Tab::make('Altro')
                            ->schema([
                                TextInput::make('pratica.cognome_cliente')->disabled(),
                                TextInput::make('pratica.nome_cliente')->disabled(),
                                TextInput::make('denominazione_riferimento')->disabled(),
                                TextInput::make('entrata_uscita')->disabled(),
                                TextInput::make('proforma_id')
                                    ->disabled()
                                    ->numeric(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull()
            ]);
    }
}
