<?php

namespace App\Filament\Resources\Praticas\Tables;


use Filament\Actions\ViewAction;

use Filament\Forms\Components\Select;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

use App\Models\PraticheStato;
use App\Models\TipoProdotto;

class PraticasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
        TextColumn::make('denominazione_agente')
        ->label('Produttore')
        ->sortable()
                    ->searchable(),


                TextColumn::make('cognome_cliente')
                ->label('Cliente')
                  ->sortable()
                    ->searchable(),
                TextColumn::make('nome_cliente')
                  ->sortable()
                    ->searchable(),

                TextColumn::make('denominazione_banca')
                  ->sortable()
                    ->searchable(),
                TextColumn::make('tipo_prodotto')
                  ->sortable()
                    ->searchable(),

                TextColumn::make('stato_pratica')
                ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('data_inserimento_pratica')
                    ->date()
                    ->sortable()
                    ->searchable(),

              TextColumn::make('codice_pratica')
                    ->searchable(),
            ])
->filters([
            SelectFilter::make('stato_pratica')
                ->options(PraticheStato::pluck('stato_pratica', 'stato_pratica'))
                ->multiple()
                ->label('Stato Pratica'),

            SelectFilter::make('tipo_prodotto')
       ->options(TipoProdotto::pluck('tipo_prodotto', 'tipo_prodotto'))
                ->multiple()
                ->label('Tipo Prodotto')
        ])
            ->recordActions([
                ViewAction::make(),
            ]);

    }
}
