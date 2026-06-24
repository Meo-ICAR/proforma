<?php

namespace App\Filament\Resources\Clientis\Tables;

use App\Filament\Exports\DynamicGroupExport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;  // CORRETTO
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class ClientisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->reorderableColumns()
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nome')
                    ->label('Fattura a')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('piva')
                    ->sortable()
                    ->searchable(),
                ToggleColumn::make('is_dummy')
                    ->label('Fittizia')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Attiva')
                    ->sortable(),
                TextColumn::make('type')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stipulated_at')
                    ->label('Stipulata il')
                    ->sortable(),
                TextColumn::make('dismissed_at')
                    ->label('Cessata il')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Attiva')
                    ->sortable(),
                TextColumn::make('id'),
            ])
            ->filters([
                TernaryFilter::make('piva')
                    ->label('Partita IVA')
                    ->placeholder('Tutti')
                    ->trueLabel('Con Partita IVA')
                    ->falseLabel('Senza Partita IVA')
                    ->queries(
                        true: fn(Builder $query) => $query->where(fn(Builder $query) => $query->where('is_active', true))->whereNotNull('piva')->where('piva', '!=', ''),
                        false: fn(Builder $query) => $query->where(fn(Builder $query) => $query->where('is_active', true))->whereNull('piva')->orWhere('piva', ''),
                        blank: fn(Builder $query) => $query,
                    ),
                TernaryFilter::make('is_dummy')
                    ->label('Fittizia')
                    ->placeholder('Tutti')
                    ->trueLabel('Fittizia')
                    ->falseLabel('Reale'),
                TernaryFilter::make('is_active')
                    ->label('Attiva')
                    ->placeholder('Tutti')
                    ->trueLabel('Attiva')
                    ->falseLabel('Non Attiva'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //   DeleteBulkAction::make(),
                    //   ForceDeleteBulkAction::make(),
                    //   RestoreBulkAction::make(),
                ]),
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
            ]);
    }
}
