<?php

namespace App\Filament\Resources\Proformas\Pages;

use App\Filament\Resources\Proformas\ProformaResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO

class EditProforma extends EditRecord
{
    protected static string $resource = ProformaResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        $record = $this->getRecord();

        return $record->fornitore
            ? "Per escludere una provvigione da questo proforma per {$record->fornitore->name}, cliccare sul simbolo rosso del bidone"
            : 'Per escludere una provvigione da questo proforma cliccare sul simbolo rosso del bidone';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('creazionePrimanota')
                ->label('Creazione Primanota')
                ->icon('heroicon-o-document-plus')
                ->form([
                    Radio::make('tipo_primanota')
                        ->label('Seleziona la tipologia')
                        ->options([
                            'spese_pratica' => 'Formazione',
                            'contributo' => 'Contributo',
                        ])
                        ->default('spese_pratica')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->modalHeading('Conferma Creazione Primanota')
                ->modalDescription('Sei sicuro di voler procedere con la creazione della primanota per questo proforma?')
                ->modalSubmitActionLabel('Conferma e Crea')
                ->action(function (array $data): void {
                    // $data['tipo_primanota'] conterrà 'spese_pratica' oppure 'contributo'
                    $tipo = $data['tipo_primanota'];

                    // Inserisci qui la tua logica di creazione della primanota
                    // $this->record contiene il modello del Proforma corrente

                    Notification::make()
                        ->title('Primanota creata con successo')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
            //  ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
