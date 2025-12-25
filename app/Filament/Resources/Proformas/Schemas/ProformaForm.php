<?php

namespace App\Filament\Resources\Proformas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;

use Filament\Forms\Components\Section;

class ProformaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Proforma')
                ->tabs([
                    Tab::make('Dati Generali')
                        ->schema([
                                     TextInput::make('anticipo')
                                        ->label('Recupero Anticipo')
                                        ->numeric()
                                        ->prefix('€'),
                                    TextInput::make('fornitore.anticipo_residuo')
                                        ->numeric()
                                        ->disabled()
                                        ->prefix('€'),
                                    TextInput::make('anticipo_descrizione')
                                        ->maxLength(255),
                                    TextInput::make('compenso')
                                        ->numeric()
                                        ->disabled()
                                        ->prefix('€'),
                                    Textarea::make('compenso_descrizione')
                                        ->columnSpanFull(),
                                        TextInput::make('contributo')
                                        ->numeric()
                                        ->prefix('€'),
                                    TextInput::make('contributo_descrizione')
                                        ->maxLength(255),
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
                                    TextInput::make('emailfrom')
                                        ->label('Da')
                                        ->email()
                                        ->required(),
                                    Textarea::make('emailbody')
                                        ->label('Corpo Email')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),


                        Tab::make('Note')
                        ->schema([
                                    Textarea::make('annotation')
                                        ->label('Note')
                                        ->columnSpanFull(),

                                ]),

                ])
                ->columnSpanFull(),
        ]);
    }
}
