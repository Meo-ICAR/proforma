<?php

namespace App\Filament\Resources\Fornitores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class FornitoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('codice')
                    ->searchable(),
                TextColumn::make('coge')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('nome')
                    ->searchable(),
                TextColumn::make('natoil')
                    ->date()
                    ->sortable(),
                TextColumn::make('indirizzo')
                    ->searchable(),
                TextColumn::make('comune')
                    ->searchable(),
                TextColumn::make('cap')
                    ->searchable(),
                TextColumn::make('prov')
                    ->searchable(),
                TextColumn::make('tel')
                    ->searchable(),
                TextColumn::make('coordinatore')
                    ->searchable(),
                TextColumn::make('piva')
                    ->searchable(),
                TextColumn::make('cf')
                    ->searchable(),
                TextColumn::make('nomecoge')
                    ->searchable(),
                TextColumn::make('nomefattura')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('anticipo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('enasarco')
                    ->badge(),
                TextColumn::make('anticipo_residuo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('contributo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('contributo_description')
                    ->searchable(),
                TextColumn::make('anticipo_description')
                    ->searchable(),
                TextColumn::make('issubfornitore')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('operatore')
                    ->searchable(),
                IconColumn::make('iscollaboratore')
                    ->boolean(),
                IconColumn::make('isdipendente')
                    ->boolean(),
                TextColumn::make('regione')
                    ->searchable(),
                TextColumn::make('citta')
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
