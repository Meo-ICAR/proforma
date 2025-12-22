<?php

namespace App\Filament\Resources\InvoiceIns\Pages;

use App\Filament\Resources\InvoiceIns\InvoiceInResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoiceIn extends EditRecord
{
    protected static string $resource = InvoiceInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
