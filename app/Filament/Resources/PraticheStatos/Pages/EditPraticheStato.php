<?php

namespace App\Filament\Resources\PraticheStatos\Pages;

use App\Filament\Resources\PraticheStatos\PraticheStatoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPraticheStato extends EditRecord
{
    protected static string $resource = PraticheStatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
