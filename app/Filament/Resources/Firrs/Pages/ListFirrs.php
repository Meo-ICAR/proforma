<?php

namespace App\Filament\Resources\Firrs\Pages;

use App\Filament\Resources\Firrs\FirrResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFirrs extends ListRecords
{
    protected static string $resource = FirrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
