<?php

namespace App\Filament\Resources\PraticheStatos\Pages;

use App\Filament\Resources\PraticheStatos\PraticheStatoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPraticheStatos extends ListRecords
{
    protected static string $resource = PraticheStatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
