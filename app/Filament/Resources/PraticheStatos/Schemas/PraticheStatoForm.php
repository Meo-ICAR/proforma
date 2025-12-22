<?php

namespace App\Filament\Resources\PraticheStatos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PraticheStatoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('isrejected')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('isworking')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('isestingued')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
