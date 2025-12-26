<?php

namespace App\Filament\Resources\Enasarcos\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class EnasarcosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('competenza'),
                TextColumn::make('enasarco')
                    ->badge(),
                TextColumn::make('minimo')
                    ->label('Da importo')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('massimo')
                    ->label('A importo')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('minimale')
                    ->label('Contributo minimo')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('massimale')
                    ->label('Contributo massimo')
                    ->money('EUR')
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
