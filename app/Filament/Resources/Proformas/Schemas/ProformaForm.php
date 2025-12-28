<?php

namespace App\Filament\Resources\Proformas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                                    ->label('Recupero mensile Anticipo ( 0 = tutto il residuo)')
                                    ->numeric()
                                    ->prefix('€'),
                                TextInput::make('anticipo_descrizione')
                                    ->maxLength(255),
                                TextInput::make('fornitore.anticipo_residuo')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('€'),
                                TextInput::make('compenso')
                                    ->label('Totale provvigioni')
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
                                TextInput::make('emailfrom')
                                    ->label('Da')
                                    ->email()
                                    ->required(),
                                Textarea::make('emailbody')
                                    ->label('Corpo Email')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
