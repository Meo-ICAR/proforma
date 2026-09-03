<?php

namespace App\Filament\Resources\Venasarcotots\Tables;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\Provvigiones\ProvvigioneResource;
use App\Models\Venasarcotot;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class VenasarcototsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withSum('trimestri', 'contributo'))
            ->reorderableColumns()
            ->columns([
                TextColumn::make('produttore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('montante')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->url(fn ($record) => ProvvigioneResource::getUrl('index').'?filters[data_fattura][has_invoice_date]=all&filters[erogated_at][has_erogated_date]=all'
                      .'filters[stato][values][0]=Pagato'.'&filters[denominazione_riferimento][denominazione_riferimento]='.$record->produttore)
                    ->openUrlInNewTab(true)
                    ->sortable(),
                TextColumn::make('contributo')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('imposta')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('credito')
                    ->label('Credito prod.')
                    ->state(fn ($record) => $record->imposta - $record->contributo == 0 ? null : $record->imposta - $record->contributo)
                    ->alignEnd()
                    ->money('EUR'),  // Puoi concatenare formattatori nativi
                TextColumn::make('RACES')
                    ->state(function ($record): float {
                        return $record->contributo / 2 + $record->imposta - $record->contributo;
                    })
                    ->alignEnd()
                    ->money('EUR'),  // Puoi concatenare formattatori nativi
                // 2. Visualizziamo il risultato
                // Filament crea automaticamente il nome: {relazione}_sum_{campo}
                TextColumn::make('trimestri_sum_contributo')
                    ->label('Versato')
                    ->money('eur')
                    ->placeholder('0,00 €'),
                TextColumn::make('Conguaglio')
                    ->state(function ($record): float {
                        return $record->contributo / 2 + $record->imposta - $record->contributo - $record->trimestri_sum_contributo / 2;
                    })
                    ->alignEnd()
                    ->money('EUR'),  // Puoi concatenare formattatori nativi
                TextColumn::make('X')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('firr')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('competenza')
                    ->sortable(),
                TextColumn::make('enasarco')
                    ->sortable()
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('competenza')
                    ->label('Anno Competenza')
                    ->options(function () {
                        // Get unique years from the competenza column
                        return Venasarcotot::query()
                            ->select('competenza')
                            ->distinct()
                            ->orderBy('competenza', 'desc')
                            ->pluck('competenza', 'competenza');
                    })
                    ->default(now()->subDays(20)->format('Y')),
            ], layout: FiltersLayout::AboveContent)
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        DynamicGroupExport::make()
                            //   ->groupBy('produttore')  // Campo per il raggruppamento
                            ->sumColumns(['montante', 'contributo', 'imposta', 'Credito prod.',  'R a c e s', 'Conguaglio', 'Versato', 'firr']),  // Campi da sommare
                    ])
                    ->label('Excel')
                    ->color('success'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
