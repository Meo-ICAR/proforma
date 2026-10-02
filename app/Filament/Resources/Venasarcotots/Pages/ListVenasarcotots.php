<?php

namespace App\Filament\Resources\Venasarcotots\Pages;

use App\Filament\Resources\Venasarcotots\VenasarcototResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListVenasarcotots extends ListRecords
{
    protected static string $resource = VenasarcototResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calcola')
                ->label('Ricalcola contributi')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                //   ->requiresConfirmation()
                ->action(function () {
                    try {
                        Artisan::call('venasarcotot:calculate');

                        Notification::make()
                            ->title('Calcolo ENASARCO e FIRR completato con successo')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Errore durante il calcolo ENASARCO')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        throw $e;
                    }
                }),
            //   CreateAction::make(),
        ];
    }
}
