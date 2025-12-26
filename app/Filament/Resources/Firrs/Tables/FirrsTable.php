<?php

namespace App\Filament\Resources\Firrs\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FirrsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('minimo')
                    ->label('Montante annuo provvigioni da')
                    ->money('EUR')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('massimo')
                    ->label('Fino a')
                    ->money('EUR')
                    ->alignRight()
                    ->sortable(),
                TextColumn::make('aliquota')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('enasarco')
                    ->badge(),
                TextColumn::make('competenza')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('competenza')
                    ->options(function () {
                        return \App\Models\Firr::query()
                            ->select('competenza')
                            ->distinct()
                            ->orderBy('competenza', 'desc')
                            ->pluck('competenza', 'competenza');
                    })
                    ->default(now()->year)
                    ->searchable(),
                SelectFilter::make('enasarco')
                    ->options([
                        'monomandatario' => 'Monomandatario',
                        'plurimandatario' => 'Plurimandatario',
                        'societa' => 'Societa',
                        'no' => 'No',
                    ])
                    ->searchable(),
            ])
            ->bulkActions([
                BulkAction::make('clone_next_year')
                    ->label('Duplica per anno successivo')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Collection $records) {
                        try {
                            DB::beginTransaction();

                            $clonedCount = 0;
                            $nextYear = null;

                            foreach ($records as $record) {
                                $newRecord = $record->replicate();
                                $nextYear = $record->competenza + 1;
                                $newRecord->competenza = $nextYear;
                                $newRecord->save();
                                $clonedCount++;
                            }

                            DB::commit();

                            if ($nextYear) {
                                $livewire->tableFilters['competenza'] = $nextYear;
                            }
                            Notification::make()
                                ->title("Clonati {$clonedCount} record per l'anno " . ($nextYear ?? ''))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                    })
                    ->deselectRecordsAfterCompletion()
            ])
            ->selectable()
            ->recordActions([
                //   EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
