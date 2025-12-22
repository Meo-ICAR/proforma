<?php

namespace App\Filament\Resources\Fornitores\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FornitoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codice'),
                TextInput::make('coge'),
                TextInput::make('name'),
                TextInput::make('nome'),
                DatePicker::make('natoil'),
                TextInput::make('indirizzo'),
                TextInput::make('comune'),
                TextInput::make('cap'),
                TextInput::make('prov'),
                TextInput::make('tel')
                    ->tel(),
                TextInput::make('coordinatore'),
                TextInput::make('piva'),
                TextInput::make('cf'),
                TextInput::make('nomecoge'),
                TextInput::make('nomefattura'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('anticipo')
                    ->numeric(),
                Select::make('enasarco')
                    ->options([
            'no' => 'No',
            'monomandatario' => 'Monomandatario',
            'plurimandatario' => 'Plurimandatario',
            'societa' => 'Societa',
        ]),
                TextInput::make('anticipo_residuo')
                    ->numeric(),
                TextInput::make('contributo')
                    ->numeric(),
                TextInput::make('contributo_description')
                    ->default('Contributo spese'),
                TextInput::make('anticipo_description')
                    ->default('Anticipo attuale'),
                TextInput::make('issubfornitore')
                    ->numeric()
                    ->default(0),
                TextInput::make('operatore'),
                Toggle::make('iscollaboratore'),
                Toggle::make('isdipendente'),
                TextInput::make('regione'),
                TextInput::make('citta'),
            ]);
    }
}
