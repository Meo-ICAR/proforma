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
                TextEntry::make('codice'),
                TextEntry::make('is_rejected')
                    ->numeric(),
                TextEntry::make('is_working')
                    ->numeric(),
                TextEntry::make('is_estingued')
                    ->numeric(),
            ]);
    }
}
