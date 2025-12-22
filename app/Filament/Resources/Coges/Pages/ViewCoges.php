<?php

namespace App\Filament\Resources\Coges\Pages;

use App\Filament\Resources\Coges\CogesResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCoges extends ViewRecord
{
    protected static string $resource = CogesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
