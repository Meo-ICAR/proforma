<?php

namespace App\Filament\Resources\PrimaNotaEntries\Tables;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\PrimaNotaConfigs\Schemas\PrimaNotaConfigForm;
use App\Models\PrimaNotaEntry;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class PrimaNotaEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('data', 'desc')
            ->columns([
                TextColumn::make('data')
                    ->label('Data')
                    ->date()
                    ->sortable(),

                TextColumn::make('config.event_label')
                    ->label('Regola')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('importo')
                    ->label('Importo')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('conto_dare')
                    ->label('Conto Dare'),

                TextColumn::make('conto_avere')
                    ->label('Conto Avere'),

                TextColumn::make('record_type')
                    ->label('Origine')
                    ->formatStateUsing(fn (?string $state) => $state ? (PrimaNotaConfigForm::MODEL_OPTIONS[$state] ?? class_basename($state)) : '—')
                    ->badge(),

                TextColumn::make('record_id')
                    ->label('ID origine'),

                ToggleColumn::make('is_active')
                    ->label('Attiva')
                    ->tooltip('Disattiva per escludere la voce dall\'invio a Business Central'),

                IconColumn::make('synced_at')
                    ->label('Inviata')
                    ->boolean()
                    ->tooltip(fn (PrimaNotaEntry $record) => $record->synced_at
                        ? 'Inviata il '.$record->synced_at->format('d/m/Y H:i')
                        : ($record->sync_error ?: 'Non ancora inviata')),

                TextColumn::make('created_at')
                    ->label('Generata il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('prima_nota_config_id')
                    ->label('Regola')
                    ->relationship('config', 'event_label'),

                SelectFilter::make('record_type')
                    ->label('Origine')
                    ->options(PrimaNotaConfigForm::MODEL_OPTIONS),

                TernaryFilter::make('is_active')
                    ->label('Attiva'),

                TernaryFilter::make('synced_at')
                    ->label('Inviata a Business Central')
                    ->nullable(),

                Filter::make('data')
                    ->schema([
                        DatePicker::make('data_da')->label('Da'),
                        DatePicker::make('data_a')->label('A'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['data_da'] ?? null, fn (Builder $q, $date) => $q->whereDate('data', '>=', $date))
                            ->when($data['data_a'] ?? null, fn (Builder $q, $date) => $q->whereDate('data', '<=', $date));
                    }),
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
            ->recordActions([
                EditAction::make(),

                Action::make('invia')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->label('Invia in contabilità')
                    ->visible(fn (PrimaNotaEntry $record) => $record->is_active && ! $record->synced_at)
                    ->action(function (PrimaNotaEntry $record) {
                        try {
                            $exitCode = Artisan::call('coge:sync-primenote', [
                                '--id' => $record->id,
                            ]);

                            if ($exitCode === 0) {
                                Notification::make()
                                    ->title('Invio completato')
                                    ->body('La voce è stata inviata correttamente a Business Central.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Errore invio dati')
                                    ->body("Si è verificato un errore durante l'invio. Controlla i log per i dettagli.")
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Log::error('Eccezione durante il richiamo del comando coge:sync-primenote: '.$e->getMessage());
                            Notification::make()
                                ->title('Errore inaspettato')
                                ->body('Si è verificato un errore imprevisto.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
