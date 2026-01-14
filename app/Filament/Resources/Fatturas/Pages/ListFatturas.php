<?php

namespace App\Filament\Resources\Fatturas\Pages;

use App\Filament\Resources\Fatturas\FatturaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFatturas extends ListRecords
{
    protected static string $resource = FatturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
