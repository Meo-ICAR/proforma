<?php

namespace App\Filament\Resources\Enasarcos\Pages;

use App\Filament\Resources\Enasarcos\EnasarcoResource;
use App\Filament\Resources\Firrs\FirrResource;
use App\Filament\Resources\Venasarcotots\VenasarcototResource;
use App\Filament\Resources\VenasarcoTrimestres\VenasarcoTrimestreResource;
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
            Action::make('firr')
                ->label('Scaglioni FIRR')
                ->color('warning')
                ->icon('heroicon-o-adjustments-vertical')
                ->url(FirrResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
