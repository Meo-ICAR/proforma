<?php

namespace App\Filament\Resources\Fornitores\Pages;

use App\Filament\Resources\Fornitores\FornitoreResource;
use App\Filament\Resources\Proformas\Schemas\ProformaEditSchema;
use App\Filament\Resources\Proformas\ProformaResource;
use App\Models\Proforma;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewFornitore extends ViewRecord
{
    protected static string $resource = FornitoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('erogaAnticipo')
                ->label('Eroga Anticipo')
                ->color('success')
                ->form([
                    ...ProformaEditSchema::configure(
                        Schema::make()
                    )->getComponents(),
                ])
                ->action(function (array $data) {
                    // Use a transaction to ensure data consistency
                    return \DB::transaction(function () use ($data) {
                        // Create the proforma
                        $proforma = Proforma::create([
                            'fornitori_id' => $this->record->id,
                            'anticipo_descrizione' => 'Anticipo provvigionale',
                            'stato' => 'Inserito',
                            'anticipo' => $data['anticipo'] ?? 0,
                            'commenti' => $data['commenti'] ?? null,
                        ]);

                        // Increment the anticipo_residuo in the Fornitore model
                        if (isset($data['anticipo']) && is_numeric($data['anticipo']) && $data['anticipo'] > 0) {
                            $this->record->increment('anticipo_residuo', $data['anticipo']);
                        }
                        return redirect()
                            ->to(ProformaResource::getUrl('edit', ['record' => $proforma]));
                    });
                }),
            EditAction::make(),
        ];
    }
}
