<?php

namespace App\Filament\Resources\Proformas\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProformaEditSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('anticipo')
                    ->label('Anticipo che si intende erogare')
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('anticipo_descrizione')
                    ->label('Causale anticipo')
                    ->maxLength(255),
                Textarea::make('annotation')
                    ->label('Commenti')
                    ->columnSpanFull(),
                TextInput::make('anticipo_residuo')
                    ->label('Anticipi finora erogati ancora da rimborsare')
                    ->numeric()
                    ->disabled()
                    ->prefix('€'),
            ]);
    }
}
