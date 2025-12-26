<?php

namespace App\Filament\Resources\Venasarcotots\Pages;

use App\Filament\Resources\Venasarcotots\VenasarcototResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Models\Firr;

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
                        DB::beginTransaction();

                        // Delete existing records
                        DB::statement('DELETE FROM venasarcotot');

                        // Insert new records from view
                        DB::statement('INSERT INTO venasarcotot (produttore, montante, contributo, X, imposta, firr, competenza, enasarco)
                                     SELECT *   FROM vwenasarcotot');

                        DB::commit();

                        $record = Venasarcotot::all();
                        foreach ($record as $record) {
                            $totalAmount = $record->montante;
                            $enasarco = $record->enasarco;
                            $competenza = $record->competenza;
                            $firr = Firr::calculateContributo($totalAmount, $enasarco, $competenza);
                            $record->update([
                                'firr' => $firr,
                            ]);
                        }
                        Notification::make()
                            ->title('Calcolo ENASARCO e FIRR completato con successo')
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
