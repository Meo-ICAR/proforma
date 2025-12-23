<?php

namespace App\Filament\Resources\Fornitores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class FornitoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('anticipo_residuo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('contributo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('enasarco')
                    ->badge()
                    ->sortable(),
                 TextColumn::make('coordinatore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('piva'),
                                
                TextColumn::make('email')
                    ->label('Email address'),
                  
           
                TextColumn::make('regione')
                    ->searchable()
 ->sortable(),
                TextColumn::make('citta')
                    ->searchable()
                     ->sortable(),
                      TextColumn::make('tel')
                    ->searchable(),
               
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
           
            ])
            ->toolbarActions([
               
                
            ]);
    }
}
