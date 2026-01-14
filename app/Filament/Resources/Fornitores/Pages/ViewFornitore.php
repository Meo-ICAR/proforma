<?php

namespace App\Filament\Resources\Fornitores\Pages;

use App\Filament\Resources\Fornitores\FornitoreResource;
use App\Filament\Resources\Proformas\Schemas\ProformaEditSchema;
use App\Filament\Resources\Proformas\ProformaResource;
use App\Models\Proforma;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
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
                ->form(function () {
                    $schema = ProformaEditSchema::configure(Schema::make());

                    // Get the components and modify them if needed
                    $components = $schema->getComponents();

                    return $components;
                })
                ->fillForm([
                    'fornitori_id' => $this->record->id,
                    'anticipo_residuo' => $this->record->anticipo_residuo,
                    'anticipo_descrizione' => 'Anticipo provvigionale',
                ])
                ->action(function (array $data) {
                    //   if (isset($data['anticipo']) && is_numeric($data['anticipo']) && $data['anticipo'] > 0) {
                    // $anticipo_residuo = $this->record->anticipo_residuo + $data['anticipo'];
                    // $this->record->anticipo_residuo = $anticipo_residuo;
                    //  $this->record->save();
                    //   } else {
                    //  Notification::make()
                    //  ->title('Anticipo non valido')
                    //    ->danger()
                    //       ->send();
                    //     return;
                    //  }
                    // Use a transaction to ensure data consistency
                    return \DB::transaction(function () use ($data) {
                        // Create the proforma
                        // $anticipo_residuo = $this->record->anticipo_residuo + $data['anticipo'];
                        $proforma = Proforma::create([
                            'fornitori_id' => $this->record->id,
                            'anticipo_residuo' => $this->record->anticipo_residuo,
                            'emailsubject' => 'Anticipo provvigionale #',
                            'emailto' => $this->record->email,
                            'emailfrom' => $this->record->company->email,
                            //  'emailcc' => $this->record->company->email_cc,
                            'anticipo_descrizione' => 'Anticipo provvigionale',
                            'stato' => 'Inserito',
                            'anticipo' => -$data['anticipo'] ?? 0,
                            'annotation' => $data['annotation'] ?? null,
                        ]);
                        $proforma->emailsubject .= $proforma->id . ' ' . $this->record->name;
                        $proforma->save();
                        return redirect()
                            ->to(ProformaResource::getUrl('edit', ['record' => $proforma]));
                    });
                }),
            EditAction::make(),
        ];
    }
}
