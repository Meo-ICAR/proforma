<?php

namespace App\Filament\Resources\Vcoges\Tables;

use App\Models\Vcoge;  // Make sure this is correctly cased
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VcogesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mese')
                    ->searchable(),
                TextColumn::make('entrata')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->label('Entrata')
                    ->sortable(),
                TextColumn::make('uscita')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->label('Uscita')
                    ->sortable(),
                TextColumn::make('saldo')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
            ])
            ->defaultSort('mese', 'desc')
            ->filters([
                SelectFilter::make('mese')
                    ->label('Mesi')
                    ->options(fn() => Vcoge::getDistinctMonths())
                    ->multiple()
                // In Filament 4.x, il valore di default viene applicato automaticamente
                // se non diversamente specificato.
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
