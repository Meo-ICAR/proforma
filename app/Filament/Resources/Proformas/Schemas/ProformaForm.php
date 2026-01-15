<?php

namespace App\Filament\Resources\Proformas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ProformaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Proforma')
                    ->tabs([
                        Tab::make('Compensi')
                            ->schema([
                                TextInput::make('anticipo')
                                    ->live()  // Fondamentale: permette alla UI di reagire ai cambiamenti in tempo reale
                                    ->label(function ($state) {
                                        // Se lo stato è nullo o uguale a 0
                                        if (blank($state) || $state == 0) {
                                            return 'Recupero mensile Anticipo ( 0 = tutto il residuo)';
                                        }

                                        // Se il valore è positivo o negativo
                                        return $state > 0
                                            ? 'Recupero mensile Anticipo ( 0 = tutto il residuo)'
                                            : 'Anticipo erogato ( il negativo indica erogazione)';
                                    })
                                    ->numeric()
                                    ->prefix('€'),
                                TextInput::make('anticipo_descrizione')
                                    ->maxLength(255),
                                TextInput::make('anticipo_residuo')
                                    ->label('Montante anticipi finora erogati da rimborsare')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->prefix('€'),
                                TextInput::make('compenso')
                                    ->label('Totale provvigioni')
                                    ->hidden(fn(callable $get) => $get('anticipo') < 0)
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('€'),
                                Textarea::make('compenso_descrizione')
                                    ->hidden(fn(callable $get) => $get('anticipo') < 0)
                                    ->columnSpanFull(),
                                TextInput::make('contributo')
                                    ->hidden(fn(callable $get) => $get('anticipo') < 0)
                                    ->numeric()
                                    ->prefix('€'),
                                TextInput::make('contributo_descrizione')
                                    ->hidden(fn(callable $get) => $get('anticipo') < 0)
                                    ->maxLength(255),
                                Textarea::make('annotation')
                                    ->label('Eventuali ns. note aggiuntve nella email')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Tab::make('Email')
                            ->schema([
                                TextInput::make('emailsubject')
                                    ->label('Oggetto')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('emailto')
                                    ->label('A')
                                    ->email()
                                    ->required(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
