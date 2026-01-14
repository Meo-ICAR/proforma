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
                        Tab::make('Info base')
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('email'),
                                TextEntry::make('piva'),
                                TextEntry::make('enasarco'),
                                TextEntry::make('anticipo_description'),
                                TextEntry::make('anticipo')
                                    ->numeric(),
                                TextEntry::make('anticipo_residuo'),
                                TextEntry::make('contributo_description'),
                                TextEntry::make('contributo')
                            ])
                            ->columns(3),
                        Tab::make('Dati Fiscali')
                            ->schema([
                                TextEntry::make('cf'),
                                TextEntry::make('tel'),
                                TextEntry::make('regione'),
                                TextEntry::make('prov'),
                                TextEntry::make('citta'),
                                TextEntry::make('comune'),
                                TextEntry::make('cap'),
                                TextEntry::make('natoil')
                                    ->date(),
                                TextEntry::make('indirizzo'),
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
