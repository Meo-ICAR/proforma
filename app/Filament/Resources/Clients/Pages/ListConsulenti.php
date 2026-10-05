<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\Tables\ConsulentiTable;
use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO
use Illuminate\Support\HtmlString;

class ListConsulenti extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getModelLabel(): string
    {
        return 'Consulente/Fornitore';
    }

    // Aggiunge il sottotitolo

    public function getSubheading(): string|Htmlable|null
    {
        // $record = $this->getRecord();

        return new HtmlString('Consulenti / Fornitori aziendali coinvolti nei processi');
    }

    public function table(Table $table): Table
    {
        return ConsulentiTable::configure($table);
    }
}
