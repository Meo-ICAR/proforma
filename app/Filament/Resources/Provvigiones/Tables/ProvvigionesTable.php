<?php

namespace App\Filament\Resources\Provvigiones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProvvigionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('segnalatore')
                  ->label('Produttore')
                    ->searchable(),
                TextColumn::make('importo')
                 ->label('Provvigione')
                  ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                    ->sortable(),

                TextColumn::make('stato')
                ->badge()
                    ->searchable(),
                TextColumn::make('data_status')
                    ->date()
                    ->sortable(),
                TextColumn::make('pratica.cognome_cliente')
                ->label('Cognome Cliente')
                    ->searchable(),
                TextColumn::make('pratica.nome_cliente')
                ->label('Nome')
                    ->searchable(),
                TextColumn::make('istituto_finanziario')
                    ->searchable(),

                TextColumn::make('id_pratica')
                    ->searchable(),
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
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
