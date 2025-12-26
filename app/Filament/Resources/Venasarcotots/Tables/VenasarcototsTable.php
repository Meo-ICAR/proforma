<?php

namespace App\Filament\Resources\Venasarcotots\Tables;

use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VenasarcototsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('produttore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('montante')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('contributo')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('X')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('imposta')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('firr')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('competenza')
                    ->sortable(),
                TextColumn::make('enasarco')
                    ->sortable()
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('competenza')
                    ->label('Anno Competenza')
                    ->options(function () {
                        // Get unique years from the competenza column
                        return \App\Models\Venasarcotot::query()
                            ->select('competenza')
                            ->distinct()
                            ->orderBy('competenza', 'desc')
                            ->pluck('competenza', 'competenza');
                    })
                    ->default(now()->subDays(20)->format('Y')),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
