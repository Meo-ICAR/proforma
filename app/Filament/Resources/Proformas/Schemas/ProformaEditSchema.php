<?php

namespace App\Filament\Resources\Proformas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProformaEditSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('anticipo')
                    ->label('Recupero Anticipo')
                    ->numeric()
                    ->prefix('€'),

                TextInput::make('fornitore.anticipo_residuo')
                    ->label('Anticipo Residuo')
                    ->numeric()
                    ->disabled()
                    ->prefix('€'),

                Textarea::make('commenti')
                    ->label('Commenti')
                    ->columnSpanFull(),
            ]);
    }
}
