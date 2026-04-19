<?php

namespace App\Filament\Resources\Proformas\Tables;

use App\Filament\Exports\DynamicGroupExport;
use App\Models\Proforma;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;  // ← Import corretto
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;

class ProformasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100, 'all'])
            ->columns([
                TextColumn::make('emailsubject')
                    ->label('Proforma')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stato')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('compenso')
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('contributo')
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('anticipo')
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modificato')
                    ->date()
                    ->sortable(),
                TextColumn::make('fornitore.name')
                    ->label('Produttore')
                    ->sortable(),
                TextColumn::make('emailto')
                    ->label('Email')
                    ->sortable(),
                TextColumn::make('delta')
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->alignEnd()
                    ->sortable(),
            ])
            ->selectable()
            ->checkIfRecordIsSelectableUsing(
                fn($record): bool => !empty($record->emailto)
            )
            ->filters([
                SelectFilter::make('stato')
                    ->label('Stato')
                    ->options([
                        'Inserito' => 'Inserito',
                        'Spedito' => 'Spedito',
                        'Pagato' => 'Pagato',
                        'Annullato' => 'Annullato',
                        // Add other statuses as needed
                    ])
                    ->multiple()
                    ->placeholder('Tutti gli stati')
                    ->default(['Inserito']),
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'Agente' => 'Agente',
                        'Istituto' => 'Istituto',
                        'Cliente' => 'Cliente',
                    ])
                    ->multiple()
                    ->placeholder('Tutti i tipi')
                    ->default(['Agente']),
                Filter::make('reconciled')
                    ->label('Riconciliato')
                    ->form([
                        Select::make('status')
                            ->label('Riconciliazione')
                            ->options([
                                'all' => 'Tutti',
                                'reconciled' => 'Riconciliati',
                                'not_reconciled' => 'Non riconciliati',
                            ])
                            ->default('all')
                    ])
                    ->query(function (Builder $query, array $data) {
                        $status = $data['status'] ?? 'all';

                        return match ($status) {
                            'reconciled' => $query->whereNotNull('invoiceable_id'),
                            'not_reconciled' => $query->whereNull('invoiceable_id'),
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $status = $data['status'] ?? 'all';

                        return match ($status) {
                            'reconciled' => 'Riconciliati',
                            'not_reconciled' => 'Non riconciliati',
                            default => null,
                        };
                    }),
                QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('sended_at')
                            ->label('Data Invio')
                            ->icon('heroicon-m-calendar'),
                    ])
            ])
            ->recordActions([
                EditAction::make()->label(false),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exports([
                        DynamicGroupExport::make()
                            ->groupBy('emailsubject')  // Campo per il raggruppamento
                            ->sumColumns(['compenso', 'contributo', 'anticipo', 'delta']),  // Campi da sommare
                    ])
                    ->label('Excel')
                    ->color('success'),
            ])
            ->toolbarActions([
                BulkAction::make('Invia')
                    ->label('Invia email Proforma (al produttore)')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->accessSelectedRecords()
                    ->action(function (Collection $records) {
                        // Process each record with a visible loop
                        $records->each(function ($record) {
                            $record->inviaEmail(false);
                        });

                        // Show success notification with count
                        Notification::make()
                            ->title(count($records) . '  proforma inviati')
                            ->success()
                            ->send();
                    }),
                // ->iconButton()
                BulkAction::make('test')
                    ->label('Simulazione invio email (a se stessi)')
                    ->color('info')
                    ->requiresConfirmation()
                    ->accessSelectedRecords()
                    ->action(function (Collection $records) {
                        // Process each record with a visible loop
                        $records->each(function ($record) {
                            $record->inviaEmail(true);
                        });

                        // Show success notification with count
                        Notification::make()
                            ->title(count($records) . '  proforma inviati')
                            ->success()
                            ->send();
                    }),
                BulkAction::make('forza')
                    ->label('Forza data invio email senza inviarla')
                    ->color('success')
                    ->requiresConfirmation()
                    ->accessSelectedRecords()
                    ->action(function (Collection $records) {
                        // Process each record with a visible loop
                        $records->each(function ($record) {
                            $record->update([
                                'stato' => 'Inviato',
                                'sended_at' => now(),
                                'data_invio' => now(),
                            ]);
                            // Update fornit ore's anticipo_residuo
                            if ($record->fornitore) {
                                $record->fornitore->increment('anticipo_residuo', -$record->anticipo);
                                \Log::info('Updated anticipo_residuo for fornitore ID: ' . $record->fornitore->id
                                    . ' by ' . $record->anticipo
                                    . '. New value: ' . $record->fornitore->anticipo_residuo);
                            };
                        });

                        // Show success notification with count
                        Notification::make()
                            ->title(count($records) . '  proforma forzata data invio')
                            ->success()
                            ->send();
                    })
                // ->iconButton()
                // ->color('primary'),
            ]);
    }
}
