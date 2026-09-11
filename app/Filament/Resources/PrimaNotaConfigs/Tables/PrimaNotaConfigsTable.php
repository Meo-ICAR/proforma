<?php

namespace App\Filament\Resources\PrimaNotaConfigs\Tables;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\PrimaNotaConfigs\Schemas\PrimaNotaConfigForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class PrimaNotaConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_label')
                    ->label('Etichetta evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model_type')
                    ->label('Modello')
                    ->formatStateUsing(fn (string $state) => PrimaNotaConfigForm::MODEL_OPTIONS[$state] ?? class_basename($state))
                    ->badge()
                    ->sortable(),

                TextColumn::make('value_field')
                    ->label('Campo valore'),

                TextColumn::make('date_field')
                    ->label('Campo data')
                    ->placeholder('—'),

                TextColumn::make('conto_dare')
                    ->label('Conto Dare'),

                TextColumn::make('conto_avere')
                    ->label('Conto Avere'),

                TextColumn::make('effective_from')
                    ->label('Attiva da')
                    ->date()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Attiva')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('entries_count')
                    ->label('Voci generate')
                    ->counts('entries')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('model_type')
                    ->label('Modello')
                    ->options(PrimaNotaConfigForm::MODEL_OPTIONS),

                TernaryFilter::make('is_active')
                    ->label('Attiva'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        DynamicGroupExport::make()
                            ->sumColumns([
                                'Entrata',
                                'Uscita',
                                'Saldo',
                                'Storno Entrata',
                                'Storno Uscita',
                            ]),  // Campi da sommare
                    ])
                    ->label('Excel')
                    ->color('success'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
