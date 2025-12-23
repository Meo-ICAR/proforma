<?php

namespace App\Filament\Resources\Praticas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PraticasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('codice_pratica')
                    ->searchable(),
                TextColumn::make('nome_cliente')
                    ->searchable(),
                TextColumn::make('cognome_cliente')
                    ->searchable(),
                TextColumn::make('codice_fiscale')
                    ->searchable(),
                TextColumn::make('denominazione_agente')
                    ->searchable(),
                TextColumn::make('partita_iva_agente')
                    ->searchable(),
                TextColumn::make('denominazione_banca')
                    ->searchable(),
                TextColumn::make('tipo_prodotto')
                    ->searchable(),
                TextColumn::make('denominazione_prodotto')
                    ->searchable(),
                TextColumn::make('data_inserimento_pratica')
                    ->date()
                    ->sortable(),
                TextColumn::make('stato_pratica')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
