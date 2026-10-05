<?php

namespace App\Filament\Resources\ProvvigioniStatos\Tables;

use App\Filament\Resources\ProvvigioniStatos\ProvvigioniStatoResource;
use App\Models\ProvvigioniStato;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProvvigioniStatosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stato')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            // Esiste uno stato con chiave vuota (provvigioni senza stato): non ha un URL di modifica valido.
            ->recordUrl(fn (ProvvigioniStato $record): ?string => filled($record->getKey())
                ? ProvvigioniStatoResource::getUrl('edit', ['record' => $record])
                : null)
            ->recordActions([
                EditAction::make()
                    ->visible(fn (ProvvigioniStato $record): bool => filled($record->getKey())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
