<?php

namespace App\Filament\Resources\Proformas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProformaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('stato')
                    ->required()
                    ->default('Inserito'),
                TextInput::make('fornitori_id'),
                TextInput::make('anticipo')
                    ->numeric(),
                TextInput::make('anticipo_descrizione'),
                TextInput::make('compenso')
                    ->numeric(),
                Textarea::make('compenso_descrizione')
                    ->columnSpanFull(),
                TextInput::make('contributo')
                    ->numeric(),
                TextInput::make('contributo_descrizione'),
                Textarea::make('annotation')
                    ->columnSpanFull(),
                TextInput::make('emailsubject')
                    ->email(),
                TextInput::make('emailto')
                    ->email(),
                Textarea::make('emailbody')
                    ->columnSpanFull(),
                TextInput::make('emailfrom')
                    ->email(),
                DateTimePicker::make('sended_at'),
                DateTimePicker::make('paid_at'),
                TextInput::make('delta')
                    ->numeric(),
                Textarea::make('delta_annotation')
                    ->columnSpanFull(),
            ]);
    }
}
