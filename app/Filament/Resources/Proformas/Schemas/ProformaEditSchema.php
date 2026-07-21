<?php

namespace App\Filament\Resources\Proformas\Schemas;

use Filament\Forms\Components\Select;
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
                    ->label('Importo da addebitare')
                    ->numeric()
                    ->prefix('€'),
                Select::make('anticipo_descrizione')
                    ->label('Causale')
                    ->options([
                        'Anticipo Provvigionale' => 'Anticipo Provvigionale',
                        'Welcome Bonus' => 'Welcome Bonus',
                        'Recupero Costi' => 'Recupero Costi',
                    ])
                    ->default('Anticipo Provvigionale')
                    ->required(),

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
