<?php

namespace App\Filament\Resources\Vcoges\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VcogesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mese')
                    ->searchable(),
                TextColumn::make('entrata')
                        ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
                TextColumn::make('uscita')
                        ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),
            ])
            ->defaultSort('mese', 'desc')
            ->filters([
                //
            ])
            ->recordActions([

            ])
            ->toolbarActions([

            ]);
    }


}
