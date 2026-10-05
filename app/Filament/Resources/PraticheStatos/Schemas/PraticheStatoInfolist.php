<?php

namespace App\Filament\Resources\PraticheStatos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PraticheStatoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('stato_pratica'),
                TextEntry::make('isrejected')
                    ->numeric(),
                TextEntry::make('isworking')
                    ->numeric(),
                TextEntry::make('isestingued')
                    ->numeric(),
            ]);
    }
}
