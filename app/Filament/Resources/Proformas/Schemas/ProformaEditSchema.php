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
                    ->label('Recupero mensile Anticipo ( 0 = tutto')
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('anticipo_descrizione')
                    ->maxLength(255),
                Textarea::make('annotation')
                    ->label('Commenti')
                    ->columnSpanFull(),
            ]);
    }
}
