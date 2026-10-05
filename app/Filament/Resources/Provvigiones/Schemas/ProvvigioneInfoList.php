<?php

namespace App\Filament\Resources\Provvigiones\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grids;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ProvvigioneInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Provvigione')
                    ->tabs([
                        Tab::make('Anagrafica')
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID Provvigione'),
                                TextEntry::make('data_inserimento_compenso')
                                    ->date(),
                                TextEntry::make('stato')
                                    ->badge(),
                                TextEntry::make('descrizione')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Dati Economici')
                            ->schema([
                                Components\Grid::make(3)
                                    ->schema([
                                        Components\TextEntry::make('importo')
                                            ->money('EUR')
                                            ->label('Importo'),
                                        Components\TextEntry::make('importo_effettivo')
                                            ->money('EUR')
                                            ->label('Importo Effettivo'),
                                        Components\TextEntry::make('quota')
                                            ->suffix('%')
                                            ->label('Quota'),
                                    ]),
                            ]),
                        Tab::make('Riferimento Pratica')
                            ->schema([
                                TextEntry::make('pratica.codice_pratica')
                                    ->label('Codice Pratica'),
                                TextEntry::make('pratica.nome_cliente')
                                    ->label('Nome Cliente'),
                                TextEntry::make('pratica.cognome_cliente')
                                    ->label('Cognome Cliente'),
                                TextEntry::make('pratica.tipo_prodotto')
                                    ->label('Tipo Prodotto'),
                                TextEntry::make('pratica.denominazione_prodotto')
                                    ->label('Prodotto'),
                                TextEntry::make('pratica.data_inserimento_pratica')
                                    ->date()
                                    ->label('Data Inserimento'),
                            ]),
                        Tab::make('Dati Aggiuntivi')
                            ->schema([
                                TextEntry::make('segnalatore')
                                    ->label('Segnalatore'),
                                TextEntry::make('istituto_finanziario')
                                    ->label('Istituto Finanziario'),
                                TextEntry::make('tipo')
                                    ->label('Tipo Provvigione'),
                            ]),
                    ]),
            ]);
    }
}
