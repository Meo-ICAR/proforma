<?php

namespace App\Filament\Resources\Fornitores\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class FornitoreInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Fornitore')
                    ->tabs([
                        Tab::make('Anagrafica')
                            ->schema([
                                TextEntry::make('nome'),
                                TextEntry::make('email'),
                                TextEntry::make('tel'),
                                TextEntry::make('regione'),
                                TextEntry::make('prov'),
                                TextEntry::make('citta'),
                                TextEntry::make('comune'),
                                TextEntry::make('cap'),
                                TextEntry::make('natoil')
                                    ->date(),
                                TextEntry::make('indirizzo'),
                            ])
                            ->columns(3),
                        Tab::make('Dati Fiscali')
                            ->schema([
                                TextEntry::make('piva'),
                                TextEntry::make('cf'),
                                TextEntry::make('enasarco'),
                                TextEntry::make('name'),
                                TextEntry::make('coge'),
                                TextEntry::make('nomecoge'),
                                TextEntry::make('nomefattura'),
                                TextEntry::make('codice'),
                            ])
                            ->columns(2),
                        Tab::make('Dati Contrattuali')
                            ->schema([
                                TextEntry::make('coordinatore'),
                                TextEntry::make('anticipo_description'),
                                TextEntry::make('anticipo_residuo')
                                    ->numeric(),
                                TextEntry::make('anticipo')
                                    ->numeric(),
                                TextEntry::make('contributo_description'),
                                TextEntry::make('contributo')
                                    ->numeric(),
                                TextEntry::make('issubfornitore')
                                    ->numeric(),
                                TextEntry::make('operatore'),
                                IconEntry::make('iscollaboratore')
                                    ->boolean(),
                                IconEntry::make('isdipendente')
                                    ->boolean(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
