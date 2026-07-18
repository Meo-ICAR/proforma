<?php

namespace App\Filament\Resources\Praticas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PraticaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SEZIONE 1: Dati Cliente
                Section::make('Pratica')
                    ->schema([
                        TextInput::make('cognome_cliente')
                            ->label('Cognome Cliente / Denominazione')
                            ->maxLength(191),
                        TextInput::make('nome_cliente')
                            ->label('Nome Cliente')
                            ->maxLength(191),
                        TextInput::make('codice_pratica')
                            ->label('Codice Pratica')
                            ->maxLength(255),
                        TextInput::make('denominazione_prodotto')
                            ->label('Nome Prodotto')
                            ->maxLength(191),
                        TextInput::make('denominazione_agente')
                            ->label('Agente / Rappresentante')
                            ->maxLength(191),
                        TextInput::make('denominazione_banca')
                            ->label('Istituto')
                            ->maxLength(191),

                    ])->columns(2),

                // SEZIONE 2: Dati Pratica e Prodotto
                Section::make('Dati Pratica e Prodotto')
                    ->schema([

                        TextInput::make('abi_name')
                            ->label('Finanziatore')
                            ->maxLength(255),

                        TextInput::make('codice_fiscale')
                            ->label('Codice Fiscale / P.IVA')
                            ->maxLength(191),
                        TextInput::make('partita_iva_agente')
                            ->label('P.IVA Agente')
                            ->maxLength(20),

                        TextInput::make('stato_pratica')
                            ->label('Stato Pratica')
                            ->maxLength(191),
                        TextInput::make('tipo_prodotto')
                            ->label('Tipo Prodotto'),

                    ])->columns(3),

                // SEZIONE 3: Dati Finanziari
                Section::make('Dati Finanziari')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Richiesto')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),
                        TextInput::make('erogato')
                            ->label('Erogato')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        TextInput::make('net')
                            ->label('Netto (Net)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        TextInput::make('rata')
                            ->label('Rata Mensile')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        TextInput::make('nrate')
                            ->label('Numero Rate')
                            ->numeric()
                            ->integer(),
                    ])->columns(3),

                // SEZIONE 5: Date di Avanzamento e Opzioni
                Section::make('Avanzamento e Opzioni')
                    ->schema([
                        DatePicker::make('data_inserimento_pratica')
                            ->label('Inserimento')
                            ->native(false) // Mostra il calendario di Filament invece di quello di default del browser
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('sended_at')
                            ->label('Invio'),

                        DatePicker::make('approved_at')
                            ->label('Approvazione'),

                        DatePicker::make('erogated_at')
                            ->label('Erogazione'),

                        DatePicker::make('rejected_at')
                            ->label('Rifiuto'),

                        Toggle::make('is_notowned')
                            ->label('Di terzi')
                            ->default(false),

                    ])->columns(3),
            ]);
    }
}
