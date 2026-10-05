<?php

namespace App\Filament\Resources\Enasarcos\Pages;

use App\Filament\Resources\Enasarcos\EnasarcoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEnasarco extends ViewRecord
{
    protected static string $resource = EnasarcoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
