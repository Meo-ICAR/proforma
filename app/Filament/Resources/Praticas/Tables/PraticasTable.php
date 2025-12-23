<?php

namespace App\Filament\Resources\Praticas\Tables;


use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PraticasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
        TextColumn::make('denominazione_agente')
        ->label('Produttore')
                    ->searchable(),


                TextColumn::make('cognome_cliente')
                    ->searchable(),
                TextColumn::make('nome_cliente')
                    ->searchable(),

                TextColumn::make('denominazione_banca')
                    ->searchable(),
                TextColumn::make('tipo_prodotto')
                    ->searchable(),

                TextColumn::make('stato_pratica')
                ->badge()
                    ->searchable(),
              TextColumn::make('codice_pratica')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);

    }
}
