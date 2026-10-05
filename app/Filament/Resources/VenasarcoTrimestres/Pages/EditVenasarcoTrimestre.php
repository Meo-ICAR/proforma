<?php

namespace App\Filament\Resources\VenasarcoTrimestres\Pages;

use App\Filament\Resources\VenasarcoTrimestres\VenasarcoTrimestreResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVenasarcoTrimestre extends EditRecord
{
    protected static string $resource = VenasarcoTrimestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
