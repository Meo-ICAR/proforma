<?php

namespace App\Filament\Resources\Fornitores\Tables;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\Praticas\PraticaResource;
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
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class FornitoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderableColumns()
            ->defaultSort('name')
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
                TextColumn::make('piva')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable(),
                TextColumn::make('dismissed_at')
                    ->label('Data Fine')
                    ->sortable(),
                TextColumn::make('stipulated_at')
                    ->label('Data Inizio')
                    ->sortable(),
                ToggleColumn::make('isdipendente')
                    ->label('Dipendente')
                    ->sortable(),
                TextColumn::make('coordinatore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('regione')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('citta')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tel')
                    ->searchable(),
                TextColumn::make('id'),
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
                TernaryFilter::make('dismissed_at')
                    ->label('Data Fine')
                    ->placeholder('Tutti')
                    ->trueLabel('Con Data Fine')
                    ->falseLabel('Senza Data Fine')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('dismissed_at'),
                        false: fn(Builder $query) => $query->whereNull('dismissed_at'),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        DynamicGroupExport::make()
                            ->groupBy('Produttore')  // Campo per il raggruppamento
                            ->sumColumns(['Provvigione']),  // Campi da sommare
                    ])
                    ->label('Excel')
                    ->color('success'),
            ])
            ->toolbarActions([]);
    }
}
