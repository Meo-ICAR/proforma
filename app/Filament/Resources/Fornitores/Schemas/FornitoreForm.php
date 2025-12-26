<?php

namespace App\Filament\Resources\Fornitores\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class FornitoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Fornitore')
                    ->tabs([
                        Tab::make('Anagrafica')
                            ->schema([
                                TextInput::make('name'),
                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email(),
                                Select::make('enasarco')
                                    ->options([
                                        'no' => 'No',
                                        'monomandatario' => 'Monomandatario',
                                        'plurimandatario' => 'Plurimandatario',
                                        'societa' => 'Societa',
                                    ]),
                                TextInput::make('piva'),
                                TextInput::make('cf'),
                            ])
                            ->columns(3),
                        Tab::make('Anticipazioni e Contributi')
                            ->schema([
                                TextInput::make('contributo_description')
                                    ->default('Contributo spese'),
                                TextInput::make('contributo')
                                    ->numeric(),
                                TextInput::make('contributoperiodicita'),
                                TextInput::make('contributodalmese'),
                                TextInput::make('anticipo_residuo')
                                    ->label('Montante anticipo da restituire')
                                    ->numeric(),
                                TextInput::make('anticipo_description')
                                    ->default('Rimborso mensile anticipo'),
                                TextInput::make('anticipo')
                                    ->label('Rimborso mensile anticipo ( 0 = unica volta)')
                                    ->numeric(),
                            ])
                            ->columns(2),
                        Tab::make('Dati Gestionali')
                            ->schema([
                                TextInput::make('coordinatore'),
                                TextInput::make('regione'),
                                TextInput::make('citta'),
                                // TextInput::make('nome'),
                                DatePicker::make('natoil'),
                                TextInput::make('indirizzo'),
                                TextInput::make('comune'),
                                TextInput::make('cap'),
                                TextInput::make('prov'),
                                TextInput::make('tel')
                                    ->tel(),
                                TextInput::make('coge')
                                    ->label('Conto COGE'),
                                TextInput::make('nomecoge')
                                    ->label('Descrizione in COGE'),
                                TextInput::make('nomefattura')
                                    ->label('Denominazione in fattura elettronica'),
                                TextInput::make('codice'),
                                TextInput::make('issubfornitore')
                                    ->numeric()
                                    ->default(0),
                                TextInput::make('operatore'),
                                Toggle::make('iscollaboratore'),
                                Toggle::make('isdipendente'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
