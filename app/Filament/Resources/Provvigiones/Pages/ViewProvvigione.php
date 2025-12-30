<?php

namespace App\Filament\Resources\Provvigiones\Pages;

use App\Filament\Resources\Provvigiones\ProvvigioneResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProvvigione extends ViewRecord
{
    protected static string $resource = ProvvigioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            // Uncomment these if you want to enable these actions
            // DeleteAction::make(),
            // ForceDeleteAction::make(),
            // RestoreAction::make(),
        ];
    }
}
