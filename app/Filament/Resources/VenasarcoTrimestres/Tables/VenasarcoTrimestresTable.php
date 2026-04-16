<?php

namespace App\Filament\Resources\VenasarcoTrimestres\Tables;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\Provvigiones\ProvvigioneResource;
use App\Models\Venasarcotot;
use App\Models\VenasarcoTrimestre;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Forms;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class VenasarcoTrimestresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderableColumns()
            ->groups([
                Group::make('Trimestre')
                    ->label('Trimestre')
                    ->collapsible(),  // SOSTITUISCE le vecchie impostazioni di groupingSettings
                Group::make('produttore')
                    ->label('Produttore')
                    ->collapsible(),  // SOSTITUISCE le vecchie impostazioni di groupingSettings
            ])
            ->filters([
                SelectFilter::make('competenza')
                    ->options(
                        fn() => VenasarcoTrimestre::query()
                            ->select('competenza')
                            ->distinct()
                            ->orderBy('competenza', 'desc')
                            ->pluck('competenza', 'competenza')
                    )
                    ->default(now()->subDays(50)->format('Y'))
                    ->searchable()
                    ->label('Anno Competenza'),
                SelectFilter::make('Trimestre')
                    ->multiple()
                    ->options([
                        '1' => '1°  Gen-Mar',
                        '2' => '2°  Apr-Giu',
                        '3' => '3°  Lug-Set',
                        '4' => '4°  Ott-Dic',
                    ])
                    ->label('Trimestre'),
            ])
            ->columns([
                TextColumn::make('Trimestre')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '1' => '1° Trimestre',
                        '2' => '2° Trimestre',
                        '3' => '3° Trimestre',
                        '4' => '4° Trimestre',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('produttore')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('enasarco')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'no' => 'gray',
                        'monomandatario' => 'info',
                        'plurimandatario' => 'success',
                        'societa' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'no' => 'No',
                        'monomandatario' => 'Monomandatario',
                        'plurimandatario' => 'Plurimandatario',
                        'societa' => 'Società',
                        default => $state,
                    }),
                TextColumn::make('montante')
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->money('EUR')
                    ->alignRight()
                    ->sortable()
                    ->url(fn($record) => ProvvigioneResource::getUrl('index') . '?tableFilters[trimestre][value]=1&filters[data_fattura][has_invoice_date]=all&filters[erogated_at][has_erogated_date]=all&filters[trimestre_erogazione][value]=' . $record->Trimestre . '&filters[denominazione_riferimento][denominazione_riferimento]=' . $record->produttore)
                    ->openUrlInNewTab(false),
                TextColumn::make('contributo')
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->money('EUR')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('azienda')
                    ->label('di cui RACES')
                    ->state(fn($record): float => $record->contributo / 2)
                    ->money('EUR')
                    ->prefix('€ ')
                    ->alignRight(),
                TextColumn::make('competenza')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('Trimestre', 'asc')
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        DynamicGroupExport::make()
                            //   ->groupBy('produttore')  // Campo per il raggruppamento
                            ->sumColumns(['montante', 'contributo', 'di cui RACES']),  // Campi da sommare
                    ])
                    ->label('Excel')
                    ->color('success'),
            ]);
    }
}
