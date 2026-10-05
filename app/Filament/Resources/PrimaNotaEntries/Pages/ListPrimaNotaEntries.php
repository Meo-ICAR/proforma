<?php

namespace App\Filament\Resources\PrimaNotaEntries\Pages;

use App\Filament\Resources\PrimaNotaEntries\PrimaNotaEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrimaNotaEntries extends ListRecords
{
    protected static string $resource = PrimaNotaEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
