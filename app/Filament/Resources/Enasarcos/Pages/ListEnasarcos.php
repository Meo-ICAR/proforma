<?php

namespace App\Filament\Resources\Enasarcos\Pages;

use App\Filament\Resources\Enasarcos\EnasarcoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnasarcos extends ListRecords
{
    protected static string $resource = EnasarcoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
