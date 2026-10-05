<?php

namespace App\Filament\Resources\Clientis\Pages;

use App\Filament\Resources\Clientis\ClientiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO
use Illuminate\Support\HtmlString;

class ListClientis extends ListRecords
{
    protected static string $resource = ClientiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    // Aggiunge il sottotitolo

    public function getSubheading(): string|Htmlable|null
    {
        // $record = $this->getRecord();

        return new HtmlString('Istituti finanziari. ATTENNZIONE: Poiche MediaFacile non riporta la partita IVA dell istituto per riconciliare le provvigioni, inserire manualmente la denominazione che appare sulle fatture e la partita IVA dell istituto, senno le fatture attive saranno scartate');
    }
}
