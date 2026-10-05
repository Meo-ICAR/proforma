<?php

namespace App\Filament\Resources\ProvvigioniStatos\Pages;

use App\Filament\Resources\ProvvigioniStatos\ProvvigioniStatoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProvvigioniStatos extends ListRecords
{
    protected static string $resource = ProvvigioniStatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
