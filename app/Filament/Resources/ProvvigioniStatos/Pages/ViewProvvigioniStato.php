<?php

namespace App\Filament\Resources\ProvvigioniStatos\Pages;

use App\Filament\Resources\ProvvigioniStatos\ProvvigioniStatoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProvvigioniStato extends ViewRecord
{
    protected static string $resource = ProvvigioniStatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
