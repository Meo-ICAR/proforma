<?php

namespace App\Filament\Resources\Venasarcotots\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VenasarcototForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('segnalatore'),
                TextInput::make('montante')
                    ->numeric(),
                TextInput::make('minima')
                    ->numeric(),
                TextInput::make('massima')
                    ->numeric(),
                TextInput::make('X'),
                TextInput::make('competenza')
                    ->numeric(),
                Select::make('enasarco')
                    ->options([
            'monomandatario' => 'Monomandatario',
            'plurimandatario' => 'Plurimandatario',
            'societa' => 'Societa',
            'no' => 'No',
        ]),
                TextInput::make('minimo')
                    ->numeric(),
                TextInput::make('massimo')
                    ->numeric(),
                TextInput::make('minimale')
                    ->numeric(),
                TextInput::make('massimale')
                    ->numeric(),
                TextInput::make('aliquota_soc')
                    ->numeric(),
                TextInput::make('aliquota_agente')
                    ->numeric(),
            ]);
    }
}
