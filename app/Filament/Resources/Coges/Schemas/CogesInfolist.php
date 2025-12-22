<?php

namespace App\Filament\Resources\Coges\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CogesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('fonte'),
                TextEntry::make('entrata_uscita'),
                TextEntry::make('conto_dare'),
                TextEntry::make('descrizione_dare'),
                TextEntry::make('conto_avere'),
                TextEntry::make('descrizione_avere'),
                TextEntry::make('annotazioni')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
