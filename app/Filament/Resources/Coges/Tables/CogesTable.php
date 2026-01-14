<?php

namespace App\Filament\Resources\Coges\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CogesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fonte')
                    ->searchable(),
                TextColumn::make('entrata_uscita')
                    ->searchable(),
                TextColumn::make('conto_avere')
                    ->searchable(),
                TextColumn::make('descrizione_avere')
                    ->searchable(),
                TextColumn::make('conto_dare')
                    ->searchable(),
                TextColumn::make('descrizione_dare')
                    ->searchable(),
                TextColumn::make('annotazioni')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
