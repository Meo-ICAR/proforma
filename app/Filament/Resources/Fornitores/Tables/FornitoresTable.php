<?php

namespace App\Filament\Resources\Fornitores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class FornitoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderableColumns()
            ->columns([
                TextColumn::make('name')
                    ->label('Produttore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('anticipo_residuo')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('contributo')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('enasarco')
                    ->badge()
                    ->sortable(),
                TextColumn::make('coordinatore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('piva')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address'),
                TextColumn::make('regione')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('citta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tel')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
