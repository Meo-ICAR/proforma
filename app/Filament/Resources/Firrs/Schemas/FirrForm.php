<?php

namespace App\Filament\Resources\Firrs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FirrForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('minimo')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('massimo')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('aliquota')
                    ->required()
                    ->numeric()
                    ->suffix('%'),
                TextInput::make('competenza')
                    ->required()
                    ->numeric()
                    ->default(now()->year),
                Select::make('enasarco')
                    ->options([
                        'monomandatario' => 'Monomandatario',
                        'plurimandatario' => 'Plurimandatario',
                        'societa' => 'Societa',
                        'no' => 'No',
                    ])
                    ->default('plurimandatario')
                    ->required(),
            ]);
    }
}
