<?php

namespace App\Filament\Resources\Enasarcos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnasarcosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('competenza'),
                TextColumn::make('enasarco')
                    ->badge(),
                TextColumn::make('minimo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('massimo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('minimale')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('massimale')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('aliquota_soc')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('aliquota_agente')
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
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
