<?php

namespace App\Filament\Resources\Fornitores\Pages;

use App\Filament\Resources\Fornitores\FornitoreResource;
use App\Filament\Resources\Proformas\ProformaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use App\Models\Proforma;

class ViewFornitore extends ViewRecord
{
    protected static string $resource = FornitoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
            Action::make('erogaAnticipo')
                ->label('Eroga Anticipo')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $proforma = Proforma::create([
                        'fornitori_id' => $this->record->id,
                        'stato' => 'Inserito', // Or whatever default status you want
                        // You might want to copy other fields from Fornitore if needed, 
                        // e.g., 'anticipo' => $this->record->anticipo
                    ]);

                    Notification::make()
                        ->title('Anticipo erogato con successo')
                        ->success()
                        ->send();
                    
                    return redirect()
                    ->to(ProformaResource::getUrl('edit', ['record' => $proforma]));
                }),
                 EditAction::make(),
            ];
           }
}
