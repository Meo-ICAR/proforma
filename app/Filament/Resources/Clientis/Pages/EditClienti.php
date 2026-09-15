<?php

namespace App\Filament\Resources\Clientis\Pages;

use App\Filament\Resources\Clientis\ClientiResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditClienti extends EditRecord
{
    protected static string $resource = ClientiResource::class;

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
                ->modalDescription('Sei sicuro di voler procedere con la creazione della primanota per questo cliente?')
                ->modalSubmitActionLabel('Conferma e Crea')
                ->action(function (array $data): void {
                    // $data['tipo_primanota'] conterrà 'spese_pratica' oppure 'contributo'
                    $tipo = $data['tipo_primanota'];

                    // Inserisci qui la tua logica di creazione della primanota
                    // $this->record contiene il modello del Cliente corrente

                    Notification::make()
                        ->title('Primanota creata con successo')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
