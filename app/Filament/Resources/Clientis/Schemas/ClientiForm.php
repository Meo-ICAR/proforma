<?php

namespace App\Filament\Resources\Clientis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('nome'),
                TextInput::make('piva'),
                TextInput::make('cf'),
                TextInput::make('coge'),
                TextInput::make('codice'),
            ]);
    }
}
