<?php

namespace App\Filament\Resources\Venasarcotots\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Forms\Components\Select;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class VenasarcototsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('segnalatore')
                    ->searchable(),
                TextColumn::make('montante')
                       ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
                TextColumn::make('minima')
                         ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
                TextColumn::make('massima')
                        ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
                TextColumn::make('X')
                 ->sortable()
                    ->searchable(),
                TextColumn::make('competenza')

                    ->sortable(),
                TextColumn::make('enasarco')
                 ->sortable()
                    ->badge(),
                TextColumn::make('minimo')
                         ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
                TextColumn::make('massimo')
                         ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
                TextColumn::make('minimale')
                         ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
                TextColumn::make('massimale')
                        ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
                TextColumn::make('aliquota_soc')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('aliquota_agente')
                    ->numeric()
                    ->sortable(),
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
                })->default(now()->subDays(20)->format('Y')),

        ])
            ->recordActions([

            ])
            ->toolbarActions([

            ]);
    }
}
