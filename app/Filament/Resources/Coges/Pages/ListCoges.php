<?php

namespace App\Filament\Resources\Coges\Pages;

use App\Filament\Resources\Coges\CogesResource;
use App\Filament\Resources\Vcoges\VcogeResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCoges extends ListRecords
{
    protected static string $resource = CogesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_provvigioni')
                ->label('Prospetto provvigioni')
                ->color('info')
                ->icon('heroicon-o-table-cells')
                ->url(VcogeResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
