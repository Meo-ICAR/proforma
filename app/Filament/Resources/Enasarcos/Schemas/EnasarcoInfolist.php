<?php

namespace App\Filament\Resources\Enasarcos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EnasarcoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('competenza')
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
