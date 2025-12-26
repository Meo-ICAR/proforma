<?php

namespace App\Filament\Resources\Vcoges\Pages;

use App\Filament\Resources\Vcoges\VcogeResource;
use App\Models\Vcoge;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListVcoges extends ListRecords
{
    protected static string $resource = VcogeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calcola')
                ->label('Ricalcola provvigioni finanziarie')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                //   ->requiresConfirmation()
                ->action(function () {
                    try {
                        // Delete existing records
                        Vcoge::truncate();

                        // Insert new records from view
                        DB::table('vcoge')->insertUsing(
                            ['mese', 'entrata', 'uscita'],
                            DB::table('vwcoge')
                        );

                        Notification::make()
                            ->title('Calcolo provvigioni completato con successo')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        DB::rollBack();

                        Notification::make()
                            ->title('Errore durante il calcolo provvigioni')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        throw $e;
                    }
                }),
            // CreateAction::make(),
        ];
    }
}
