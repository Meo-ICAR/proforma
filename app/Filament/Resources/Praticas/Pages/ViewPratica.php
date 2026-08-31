<?php

namespace App\Filament\Resources\Praticas\Pages;

use App\Filament\Resources\Praticas\PraticaResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPratica extends ViewRecord
{
    protected static string $resource = PraticaResource::class;

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
                            'spese_pratica' => 'Spese pratica',
                            'contributo' => 'Contributo',
                        ])
                        ->default('spese_pratica')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->modalHeading('Conferma Creazione Primanota')
                ->modalDescription('Sei sicuro di voler procedere con la creazione della primanota per questa pratica?')
                ->modalSubmitActionLabel('Conferma e Crea')
                ->action(function (array $data): void {
                    // $data['tipo_primanota'] conterrà 'spese_pratica' oppure 'contributo'
                    $tipo = $data['tipo_primanota'];

                    // Inserisci qui la tua logica di creazione della primanota
                    // $this->record contiene il modello della Pratica corrente

                    Notification::make()
                        ->title('Primanota creata con successo')
                        ->success()
                        ->send();
                }),
        ];
    }
}
