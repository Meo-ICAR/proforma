<?php

namespace App\Filament\Resources\Venasarcotots\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VenasarcototInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('segnalatore')
                    ->placeholder('-'),
                TextEntry::make('montante')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('minima')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('massima')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('X')
                    ->placeholder('-'),
                TextEntry::make('competenza')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('enasarco')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('minimo')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('massimo')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('minimale')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('massimale')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('aliquota_soc')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('aliquota_agente')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
