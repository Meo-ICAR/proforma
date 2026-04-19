<?php

namespace App\Filament\Resources\Clientis\Tables;

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

class ClientisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
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
                TextColumn::make('coge')
                    ->sortable()
                    ->searchable(),
                ToggleColumn::make('is_dummy')
                    ->label('Fittizia')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Attiva')
                    ->sortable(),
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
            ]);
    }
}
