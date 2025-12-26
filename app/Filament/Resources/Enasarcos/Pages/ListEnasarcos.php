<?php

namespace App\Filament\Resources\Enasarcos\Pages;

use App\Filament\Resources\Enasarcos\EnasarcoResource;
use App\Filament\Resources\Venasarcotots\VenasarcototResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEnasarcos extends ListRecords
{
    protected static string $resource = EnasarcoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_venasarco')
                ->label('Visualizza Contributi')
                ->color('info')
                ->icon('heroicon-o-table-cells')
                ->url(VenasarcototResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
