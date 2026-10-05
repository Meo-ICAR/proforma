<?php

namespace App\Filament\Resources\Provvigiones\Pages;

use App\Filament\Resources\Provvigiones\ProvvigioneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO
use Illuminate\Support\HtmlString;

class ListProvvigiones extends ListRecords
{
    protected static string $resource = ProvvigioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //  CreateAction::make(),
        ];
    }

    // Aggiunge il sottotitolo
    public function getSubheading(): string|Htmlable|null
    {
        // $record = $this->getRecord();

        return new HtmlString("Seleziando le provvigioni di cui si vuole emettere proforma comparira il tasto EMETTI PROFORMA.<br> Premendo il tasto verranno creati i  proforma relativi alle provvigioni selezionate . Per sospendere al mese prossimo una provvigione cliccare sul simbolo prima di 'Inserito'");
    }
}
