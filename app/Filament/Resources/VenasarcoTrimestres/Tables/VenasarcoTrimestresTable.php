<?php

namespace App\Filament\Resources\VenasarcoTrimestres\Tables;

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
                        fn() => \App\Models\VenasarcoTrimestre::query()
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
                    ->sortable(),
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
            ->defaultSort('Trimestre', 'asc');
    }
}
