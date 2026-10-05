<?php

namespace App\Filament\Resources\Proformas\Pages;

use App\Filament\Resources\Proformas\ProformaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO

class ListProformas extends ListRecords
{
    protected static string $resource = ProformaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    // Aggiunge il sottotitolo
    public function getSubheading(): string|Htmlable|null
    {
        // $record = $this->getRecord();

        return 'Selezionare i proforma da da inviare e quindi premere il tasto Invia. Per modificare un proforma cliccare sulla riga.';
    }
}
