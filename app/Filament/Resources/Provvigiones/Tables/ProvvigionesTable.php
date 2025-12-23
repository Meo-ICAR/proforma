<?php

namespace App\Filament\Resources\Provvigiones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProvvigionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('data_inserimento_compenso')
                    ->date()
                    ->sortable(),
                TextColumn::make('descrizione')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->searchable(),
                TextColumn::make('importo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('importo_effettivo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_compenso')
                    ->searchable(),
                TextColumn::make('data_pagamento')
                    ->date()
                    ->sortable(),
                TextColumn::make('n_fattura')
                    ->searchable(),
                TextColumn::make('data_fattura')
                    ->date()
                    ->sortable(),
                TextColumn::make('data_status')
                    ->date()
                    ->sortable(),
                TextColumn::make('denominazione_riferimento')
                    ->searchable(),
                TextColumn::make('entrata_uscita')
                    ->searchable(),
                TextColumn::make('id_pratica')
                    ->searchable(),
                TextColumn::make('segnalatore')
                    ->searchable(),
                TextColumn::make('istituto_finanziario')
                    ->searchable(),
                TextColumn::make('piva')
                    ->searchable(),
                TextColumn::make('cf')
                    ->searchable(),
                IconColumn::make('annullato')
                    ->boolean(),
                IconColumn::make('coordinamento')
                    ->boolean(),
                TextColumn::make('stato')
                    ->searchable(),
                TextColumn::make('proforma_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('legacy_id')
                    ->searchable(),
                TextColumn::make('invoice_number')
                    ->searchable(),
                TextColumn::make('cognome')
                    ->searchable(),
                TextColumn::make('quota')
                    ->searchable(),
                TextColumn::make('nome')
                    ->searchable(),
                TextColumn::make('fonte')
                    ->searchable(),
                TextColumn::make('tipo_pratica')
                    ->searchable(),
                TextColumn::make('data_inserimento_pratica')
                    ->date()
                    ->sortable(),
                TextColumn::make('data_stipula')
                    ->date()
                    ->sortable(),
                TextColumn::make('prodotto')
                    ->searchable(),
                TextColumn::make('macrostatus')
                    ->searchable(),
                TextColumn::make('status_pratica')
                    ->searchable(),
                TextColumn::make('status_pagamento')
                    ->searchable(),
                TextColumn::make('data_status_pratica')
                    ->date()
                    ->sortable(),
                TextColumn::make('montante')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('importo_erogato')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sended_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('received_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('paided_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
