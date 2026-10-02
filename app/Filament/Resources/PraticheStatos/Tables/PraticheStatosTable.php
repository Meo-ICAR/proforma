<?php

namespace App\Filament\Resources\PraticheStatos\Tables;

use App\Filament\Resources\PraticheStatos\PraticheStatoResource;
use App\Models\PraticheStato;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PraticheStatosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stato_pratica')
                    ->searchable(),
                TextColumn::make('isrejected')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('isworking')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('isestingued')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            // Esiste uno stato con chiave vuota (pratiche senza stato): non ha un URL di dettaglio valido.
            ->recordUrl(fn (PraticheStato $record): ?string => filled($record->getKey())
                ? PraticheStatoResource::getUrl('view', ['record' => $record])
                : null)
            ->recordActions([
                ViewAction::make()
                    ->visible(fn (PraticheStato $record): bool => filled($record->getKey())),
                EditAction::make()
                    ->visible(fn (PraticheStato $record): bool => filled($record->getKey())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
