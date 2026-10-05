<?php

namespace App\Filament\Resources\VenasarcoTrimestres\Pages;

use App\Filament\Resources\VenasarcoTrimestres\VenasarcoTrimestreResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVenasarcoTrimestre extends ViewRecord
{
    protected static string $resource = VenasarcoTrimestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
