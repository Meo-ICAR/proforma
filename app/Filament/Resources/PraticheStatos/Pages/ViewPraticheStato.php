<?php

namespace App\Filament\Resources\PraticheStatos\Pages;

use App\Filament\Resources\PraticheStatos\PraticheStatoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPraticheStato extends ViewRecord
{
    protected static string $resource = PraticheStatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
