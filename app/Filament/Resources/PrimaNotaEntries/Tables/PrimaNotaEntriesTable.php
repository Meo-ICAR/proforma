<?php

namespace App\Filament\Resources\PrimaNotaEntries\Tables;

use App\Filament\Exports\DynamicGroupExport;
use App\Filament\Resources\Clientis\ClientiResource;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Fornitores\FornitoreResource;
use App\Filament\Resources\Praticas\PraticaResource;
use App\Filament\Resources\PrimaNotaConfigs\Schemas\PrimaNotaConfigForm;
use App\Filament\Resources\Proformas\ProformaResource;
use App\Filament\Resources\Provvigiones\ProvvigioneResource;
use App\Models\Client;
use App\Models\Clienti;
use App\Models\Fornitore;
use App\Models\Pratica;
use App\Models\PrimaNotaEntry;
use App\Models\Proforma;
use App\Models\Provvigione;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;

class PrimaNotaEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('data', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with([
                'config',
                'record' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Proforma::class => ['fornitore', 'cliente', 'client'],
                ]),
            ]))
            ->columns([
                TextColumn::make('data')
                    ->label('Data')
                    ->date()
                    ->weight(FontWeight::SemiBold)
                    ->sortable(),

                TextColumn::make('importo')
                    ->label('Importo')
                    ->money('EUR')
                    ->weight(FontWeight::SemiBold)
                    ->color(fn (PrimaNotaEntry $record) => $record->importo < 0 ? 'danger' : 'success')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('conto_dare')
                    ->label('Conto Dare')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('conto_avere')
                    ->label('Conto Avere')
                    ->badge()
                    ->color('success'),

                TextColumn::make('record_type')
                    ->label('Origine')
                    ->formatStateUsing(fn (?string $state) => $state ? (PrimaNotaConfigForm::MODEL_OPTIONS[$state] ?? class_basename($state)) : '—')
                    ->description(fn (PrimaNotaEntry $record) => $record->record_id ? "ID: {$record->record_id}" : null)
                    ->badge(),

                TextColumn::make('record_name')
                    ->label('Nome')
                    ->getStateUsing(fn (PrimaNotaEntry $record) => self::resolveRecordName($record->record))
                    ->url(fn (PrimaNotaEntry $record) => self::resolveRecordUrl($record->record))
                    ->openUrlInNewTab()
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('controparte')
                    ->label('Controparte (Proforma)')
                    ->getStateUsing(fn (PrimaNotaEntry $record) => $record->proformaControparte()?->name)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('config.name')
                    ->label('Regola')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Attiva')
                    ->tooltip('Disattiva per escludere la voce dall\'invio a Business Central'),

                IconColumn::make('synced_at')
                    ->label('Inviata')
                    ->boolean()
                    ->tooltip(fn (PrimaNotaEntry $record) => $record->synced_at
                        ? 'Inviata il '.$record->synced_at->format('d/m/Y H:i')
                        : ($record->sync_error ?: 'Non ancora inviata')),

                TextColumn::make('record_id')
                    ->label('ID origine')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Generata il')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('prima_nota_config_id')
                    ->label('Regola')
                    ->relationship('config', 'name'),

                SelectFilter::make('record_type')
                    ->label('Origine')
                    ->options(PrimaNotaConfigForm::MODEL_OPTIONS),

                SelectFilter::make('fornitore_id')
                    ->label('Fornitore')
                    ->options(fn () => Fornitore::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        // Il fornitore può essere il record diretto (PrimaNotaConfig su Fornitore)
                        // oppure la controparte di un Proforma collegato (Proforma::fornitore(), via fornitori_id).
                        return $query->where(
                            fn (Builder $q) => $q
                                ->where(fn (Builder $q) => $q->where('record_type', Fornitore::class)->where('record_id', $value))
                                ->orWhereHasMorph('record', [Proforma::class], fn (Builder $q) => $q->where('fornitori_id', $value))
                        );
                    }),

                SelectFilter::make('clienti_id')
                    ->label('Istituto/Mandante (Clienti)')
                    ->options(fn () => Clienti::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        // Stessa colonna fornitori_id di Proforma::fornitore(), ma verso la tabella
                        // clientis (Proforma::cliente()) — vedi app/Models/Proforma.php.
                        return $query->where(
                            fn (Builder $q) => $q
                                ->where(fn (Builder $q) => $q->where('record_type', Clienti::class)->where('record_id', $value))
                                ->orWhereHasMorph('record', [Proforma::class], fn (Builder $q) => $q->where('fornitori_id', $value))
                        );
                    }),

                SelectFilter::make('client_id')
                    ->label('Cliente/Consulente')
                    ->options(fn () => Client::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        // Raggiungibile solo tramite Proforma::client() (client_id): non esiste ancora
                        // un model_type "Client" diretto in PrimaNotaConfigForm::MODEL_OPTIONS.
                        return $query->whereHasMorph(
                            'record',
                            [Proforma::class],
                            fn (Builder $q) => $q->where('client_id', $value)
                        );
                    }),

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
                Action::make('generaPrimanota')
                    ->label('Genera Prima Nota')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Genera Prima Nota')
                    ->modalDescription('Esegue la scansione delle regole configurate (PrimaNotaConfig) e genera le nuove voci di prima nota per gli eventi non ancora coperti. Procedere?')
                    ->modalSubmitActionLabel('Genera')
                    ->action(function () {
                        try {
                            Artisan::call('primanota:generate');

                            Notification::make()
                                ->title('Scansione completata')
                                ->body('Generazione della prima nota terminata. Aggiorna la lista per vedere le nuove voci.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Log::error('Eccezione durante il richiamo del comando primanota:generate: '.$e->getMessage());
                            Notification::make()
                                ->title('Errore inaspettato')
                                ->body('Si è verificato un errore imprevisto durante la generazione.')
                                ->danger()
                                ->send();
                        }
                    }),

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

                    BulkAction::make('inviaSelezionati')
                        ->label('Invia in contabilità')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Invia in contabilità')
                        ->modalDescription('Invia a Business Central le voci selezionate ancora attive e non inviate. Le voci già inviate o disattivate vengono ignorate.')
                        ->modalSubmitActionLabel('Invia')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $ids = $records
                                ->filter(fn (PrimaNotaEntry $record) => $record->is_active && ! $record->synced_at)
                                ->pluck('id');

                            if ($ids->isEmpty()) {
                                Notification::make()
                                    ->title('Nessuna voce da inviare')
                                    ->body('Le voci selezionate sono già state inviate o non sono attive.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            try {
                                $exitCode = Artisan::call('coge:sync-primenote', [
                                    '--ids' => $ids->implode(','),
                                ]);

                                if ($exitCode === 0) {
                                    Notification::make()
                                        ->title('Invio completato')
                                        ->body("{$ids->count()} voci inviate correttamente a Business Central.")
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Errore invio dati')
                                        ->body("Si è verificato un errore durante l'invio. Controlla i log per i dettagli.")
                                        ->danger()
                                        ->send();
                                }
                            } catch (\Throwable $e) {
                                Log::error('Eccezione durante il richiamo del comando coge:sync-primenote (invio multiplo): '.$e->getMessage());
                                Notification::make()
                                    ->title('Errore inaspettato')
                                    ->body('Si è verificato un errore imprevisto.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }

    /**
     * Risolve un'etichetta leggibile per il record polimorfico collegato,
     * qualunque sia il modello (uno dei PrimaNotaConfigForm::MODEL_OPTIONS).
     */
    protected static function resolveRecordName(?Model $record): ?string
    {
        return match (true) {
            $record === null => null,
            $record instanceof Fornitore, $record instanceof Clienti, $record instanceof Client => $record->name,
            $record instanceof Proforma => $record->emailsubject,
            $record instanceof Pratica => $record->codice_pratica,
            $record instanceof Provvigione => $record->descrizione,
            default => null,
        };
    }

    /**
     * Risolve l'URL della pagina Filament (view se disponibile, altrimenti edit)
     * del record polimorfico collegato, per rendere cliccabile la colonna "Nome".
     */
    protected static function resolveRecordUrl(?Model $record): ?string
    {
        return match (true) {
            $record instanceof Fornitore => FornitoreResource::getUrl('view', ['record' => $record]),
            $record instanceof Clienti => ClientiResource::getUrl('edit', ['record' => $record]),
            $record instanceof Client => ClientResource::getUrl('edit', ['record' => $record]),
            $record instanceof Proforma => ProformaResource::getUrl('edit', ['record' => $record]),
            $record instanceof Pratica => PraticaResource::getUrl('view', ['record' => $record]),
            $record instanceof Provvigione => ProvvigioneResource::getUrl('view', ['record' => $record]),
            default => null,
        };
    }
}
