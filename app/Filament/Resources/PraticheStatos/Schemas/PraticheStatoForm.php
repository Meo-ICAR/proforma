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
                TextInput::make('is_rejected')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('is_working')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('is_estingued')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
