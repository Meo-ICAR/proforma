<?php

namespace App\Filament\Resources\Vcoges\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VcogeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('mese'),
                TextInput::make('entrata')
                    ->numeric(),
                TextInput::make('uscita')
                    ->numeric(),
            ]);
    }
}
