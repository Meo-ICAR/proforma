<?php

namespace App\Filament\Resources\PrimaNotaConfigs\Pages;

use App\Filament\Resources\PrimaNotaConfigs\PrimaNotaConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrimaNotaConfigs extends ListRecords
{
    protected static string $resource = PrimaNotaConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
