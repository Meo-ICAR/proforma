<?php

namespace App\Filament\Resources\Coges\Pages;

use App\Filament\Resources\Coges\CogesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCoges extends EditRecord
{
    protected static string $resource = CogesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
