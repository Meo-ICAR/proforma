<?php

namespace App\Filament\Resources\Praticas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PraticaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('codice_pratica'),
                TextInput::make('nome_cliente'),
                TextInput::make('cognome_cliente'),
                TextInput::make('codice_fiscale'),
                TextInput::make('denominazione_agente'),
                TextInput::make('partita_iva_agente'),
                TextInput::make('denominazione_banca'),
                TextInput::make('tipo_prodotto'),
                TextInput::make('denominazione_prodotto'),
                DatePicker::make('data_inserimento_pratica'),
                TextInput::make('stato_pratica'),
            ]);
    }
}
