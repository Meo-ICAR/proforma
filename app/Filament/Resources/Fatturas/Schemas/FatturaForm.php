<?php

namespace App\Filament\Resources\Fatturas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FatturaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('stato')
                    ->required()
                    ->default('Inserito'),
                TextInput::make('clienti_id'),
                TextInput::make('compenso')
                    ->numeric(),
                Textarea::make('annotation')
                    ->columnSpanFull(),
                DateTimePicker::make('paid_at'),
                TextInput::make('delta')
                    ->numeric(),
                Textarea::make('delta_annotation')
                    ->columnSpanFull(),
            ]);
    }
}
