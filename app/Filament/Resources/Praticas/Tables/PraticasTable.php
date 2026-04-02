<?php

namespace App\Filament\Resources\Praticas\Tables;

use App\Models\PraticheStato;
use App\Models\TipoProdotto;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PraticasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderableColumns()
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
                TextColumn::make('erogated_at')
                    ->label('Data Erogazione')
                    ->date()
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
                    ->label('Stato Pratica')
                    ->default(['PERFEZIONATA', 'IN AMMORTAMENTO']),
                SelectFilter::make('tipo_prodotto')
                    ->options(TipoProdotto::pluck('tipo_prodotto', 'tipo_prodotto'))
                    ->multiple()
                    ->label('Tipo Prodotto'),
                Filter::make('data_fattura')
                    ->form([
                        Select::make('has_erogated_date')
                            ->label('Data erogazione')
                            ->options([
                                'all' => 'Tutti',
                                'has_date' => 'Presente',
                                'no_date' => 'Assente',
                            ])
                            ->default('all')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $hasPaymentDate = $data['has_erogated_date'] ?? 'all';

                        return match ($hasPaymentDate) {
                            'has_date' => $query->whereNotNull('erogated_at'),
                            'no_date' => $query->whereNull('erogated_at'),
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $hasPaymentDate = $data['has_erogated_date'] ?? 'all';

                        return match ($hasPaymentDate) {
                            'has_date' => 'Abbinato a fattura',
                            'no_date' => 'Non abbinato a fattura',
                            default => null,
                        };
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                // ViewAction::make(),
            ]);
    }
}
