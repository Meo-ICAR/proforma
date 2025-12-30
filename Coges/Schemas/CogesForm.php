<?php

namespace App\Filament\Resources\Coges\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CogesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('fonte')
                    ->required(),
                TextInput::make('entrata_uscita')
                    ->required(),
                TextInput::make('conto_dare')
                    ->required(),
                TextInput::make('descrizione_dare')
                    ->required(),
                TextInput::make('conto_avere')
                    ->required(),
                TextInput::make('descrizione_avere')
                    ->required(),
                TextInput::make('annotazioni'),
            ]);
    }
}
