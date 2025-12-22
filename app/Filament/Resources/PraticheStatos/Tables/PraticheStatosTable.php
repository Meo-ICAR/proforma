<?php

namespace App\Filament\Resources\PraticheStatos\Tables;

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
