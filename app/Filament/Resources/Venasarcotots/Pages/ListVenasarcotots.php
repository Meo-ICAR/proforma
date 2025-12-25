<?php

namespace App\Filament\Resources\Venasarcotots\Pages;

use App\Filament\Resources\Venasarcotots\VenasarcototResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVenasarcotots extends ListRecords
{
    protected static string $resource = VenasarcototResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
