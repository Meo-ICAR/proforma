<?php

namespace App\Filament\Resources\Vcoges\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VcogeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('mese')
                    ->placeholder('-'),
                TextEntry::make('entrata')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('uscita')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
