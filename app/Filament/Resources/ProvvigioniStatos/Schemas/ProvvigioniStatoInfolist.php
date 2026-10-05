<?php

namespace App\Filament\Resources\ProvvigioniStatos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProvvigioniStatoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('stato'),
            ]);
    }
}
