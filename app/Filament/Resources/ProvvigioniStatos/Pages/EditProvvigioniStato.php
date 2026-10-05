<?php

namespace App\Filament\Resources\ProvvigioniStatos\Pages;

use App\Filament\Resources\ProvvigioniStatos\ProvvigioniStatoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProvvigioniStato extends EditRecord
{
    protected static string $resource = ProvvigioniStatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
