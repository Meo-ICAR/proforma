<?php

namespace App\Filament\Resources\PrimaNotaConfigs\Pages;

use App\Filament\Resources\PrimaNotaConfigs\PrimaNotaConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrimaNotaConfig extends EditRecord
{
    protected static string $resource = PrimaNotaConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
