<?php

namespace App\Filament\Resources\Fornitores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->label('Email')
                    ->sortable(),
                ToggleColumn::make('isdipendente')
                    ->label('Dipendente')
                    ->sortable(),
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
                TernaryFilter::make('email')
                    ->label('Email')
                    ->placeholder('Tutti')
                    ->trueLabel('Con Email')
                    ->falseLabel('Senza Email')
                    ->queries(
                        true: fn(Builder $query) => $query->where(fn(Builder $query) => $query->where('isdipendente', false))->whereNotNull('email')->where('email', '!=', ''),
                        false: fn(Builder $query) => $query->where(fn(Builder $query) => $query->where('isdipendente', false))->whereNull('email')->orWhere('email', ''),
                        blank: fn(Builder $query) => $query,
                    ),
                TernaryFilter::make('enasarco')
                    ->label('Enasarco')
                    ->placeholder('Tutti')
                    ->trueLabel('Con Enasarco')
                    ->falseLabel('Senza Enasarco')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('enasarco')->where('enasarco', '!=', ''),
                        false: fn(Builder $query) => $query->whereNull('enasarco')->orWhere('enasarco', ''),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
