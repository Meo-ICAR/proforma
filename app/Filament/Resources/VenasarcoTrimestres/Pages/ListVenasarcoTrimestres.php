<?php

namespace App\Filament\Resources\VenasarcoTrimestres\Pages;

use App\Filament\Resources\VenasarcoTrimestres\VenasarcoTrimestreResource;
use App\Models\Venasarcotrimestre;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListVenasarcoTrimestres extends ListRecords
{
    protected static string $resource = VenasarcoTrimestreResource::class;

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
                        // Delete existing records
                        VenasarcoTrimestre::truncate();

                        // Insert new records from view
                        DB::table('venasarcotrimestre')->insertUsing(
                            ['produttore', 'montante', 'competenza', 'Trimestre', 'enasarco', 'contributo'], DB::table('vwenasarcotrimestre')->select('produttore', 'montante', 'competenza', 'Trimestre', 'enasarco', 'contributo')
                        );

                        Notification::make()
                            ->title('Calcolo trimestrale ENASARCO completato con successo')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        DB::rollBack();

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
