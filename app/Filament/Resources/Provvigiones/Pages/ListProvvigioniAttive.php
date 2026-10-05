<?php

namespace App\Filament\Resources\Provvigiones\Pages;

use App\Filament\Resources\Provvigiones\Tables\AttiveTable;
use App\Filament\Resources\Provvigiones\ProvvigioneResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO
use Illuminate\Support\HtmlString;

class ListProvvigioniAttive extends ListRecords
{
    protected static string $resource = ProvvigioneResource::class;

    public function table(Table $table): Table
    {
        return AttiveTable::configure($table);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    // Aggiunge il sottotitolo
    public function getSubheading(): string|Htmlable|null
    {
        // $record = $this->getRecord();

        return new HtmlString('Provvigioni attive. Una volta ricevuto il proforma selezionare istituto, cercare il cliente e spuntare il quadratino. Possibile anche forzare la maturazione di alcune provvigioni con icona in verde');
    }
}
