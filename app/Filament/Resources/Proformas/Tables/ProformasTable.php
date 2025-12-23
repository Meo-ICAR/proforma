<?php

namespace App\Filament\Resources\Proformas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProformasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                  TextColumn::make('id')
                  ->sortable()
                    ->searchable(),
                 TextColumn::make('fornitore.name')
                    ->label('Fornitore')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stato')
                  ->sortable()
                    ->searchable(),


                TextColumn::make('updated_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('sended_at')
                    ->date()
                    ->sortable(),
                                    TextColumn::make('compenso')
                    ->numeric()
                    ->sortable(),



                TextColumn::make('contributo')
                    ->numeric()
                    ->sortable(),
                       TextColumn::make('anticipo')
                    ->numeric()
                    ->sortable(),
 TextColumn::make('paid_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('delta')
                    ->numeric()
                    ->sortable(),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
