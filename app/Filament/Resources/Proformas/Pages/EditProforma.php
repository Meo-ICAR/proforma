<?php

namespace App\Filament\Resources\Proformas\Pages;

use App\Filament\Resources\Proformas\ProformaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO

class EditProforma extends EditRecord
{
    protected static string $resource = ProformaResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        // $record = $this->getRecord();

        return 'Per escludere una provvigione da questo proforma cliccare sul simbolo rosso del bidone';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            //  ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
