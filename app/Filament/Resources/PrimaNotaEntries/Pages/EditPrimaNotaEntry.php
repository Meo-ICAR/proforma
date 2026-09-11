<?php

namespace App\Filament\Resources\PrimaNotaEntries\Pages;

use App\Filament\Resources\PrimaNotaEntries\PrimaNotaEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrimaNotaEntry extends EditRecord
{
    protected static string $resource = PrimaNotaEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
