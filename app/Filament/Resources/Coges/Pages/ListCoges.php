<?php

namespace App\Filament\Resources\Coges\Pages;

use App\Filament\Resources\Coges\CogesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoges extends ListRecords
{
    protected static string $resource = CogesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
