<?php

namespace App\Filament\Resources\Enasarcos\Pages;

use App\Filament\Resources\Enasarcos\EnasarcoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEnasarco extends EditRecord
{
    protected static string $resource = EnasarcoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
