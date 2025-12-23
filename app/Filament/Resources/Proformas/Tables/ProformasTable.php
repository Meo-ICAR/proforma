<?php

namespace App\Filament\Resources\Proformas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProformasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stato')
                    ->searchable(),
                TextColumn::make('fornitori_id')
                    ->searchable(),
                TextColumn::make('anticipo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('anticipo_descrizione')
                    ->searchable(),
                TextColumn::make('compenso')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('contributo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('contributo_descrizione')
                    ->searchable(),
                TextColumn::make('emailsubject')
                    ->searchable(),
                TextColumn::make('emailto')
                    ->searchable(),
                TextColumn::make('emailfrom')
                    ->searchable(),
                TextColumn::make('sended_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delta')
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
