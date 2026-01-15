<?php

namespace App\Filament\Resources\Provvigiones\Tables;

use App\Filament\Resources\Praticas\PraticaResource;
use App\Models\Compenso;
use App\Models\Proforma;
use App\Models\Provvigione;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\RestoreBulkAction;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;  // ← Import corretto
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as Builderq;

class AttiveTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Provvigione::query()
                ->where('entrata_uscita', 'Entrata')
                ->whereNot('importo', 0)
                ->where('descrizione', 'not like', '%liente%'))
            ->reorderableColumns()
            ->selectable()
            ->checkIfRecordIsSelectableUsing(
                fn(Model $record): bool => $record->stato === 'Inserito'
            )
            ->headerActions([
                BulkAction::make('emetti')
                    ->label('Associa a Proforma')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->accessSelectedRecords()
                    ->requiresConfirmation()
                    ->accessSelectedRecords()
                    ->action(function (Collection $records) {
                        // Process each record with a visible loop
                        $records->each(function ($record) {
                            $record->update([
                                'stato' => 'Proforma',
                            ]);
                        });

                        // Show success notification with count
                        Notification::make()
                            ->title(count($records) . ' provvigioni abbinate a proforma')
                            ->success()
                            ->send();
                    }),
                BulkAction::make('forza')
                    ->label('Annulla Provvigioni')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->accessSelectedRecords()
                    ->action(function (Collection $records) {
                        // Process each record with a visible loop
                        $records->each(function ($record) {
                            $record->update([
                                'stato' => 'Annullato',
                            ]);
                        });

                        // Show success notification with count
                        Notification::make()
                            ->title(count($records) . ' provvigioni abbinate a proforma')
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                TextColumn::make('stato')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Inserito' => 'warning',
                        'Sospeso' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('istituto_finanziario')
                    ->label('Istituto Finanziario')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('importo')
                    ->label('Provvigione')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label('')->query(fn(Builderq $query) => $query->where('stato', 'Inserito')))
                    ->sortable(),
                TextColumn::make('data_status')
                    ->date()
                    ->sortable(),
                TextColumn::make('pratica.cognome_cliente')
                    ->label('Cognome Cliente')
                    ->searchable(),
                TextColumn::make('pratica.nome_cliente')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('id_pratica')
                    ->label('Pratica')
                    ->color('info')
                    ->url(fn($record) => PraticaResource::getUrl('view', ['record' => $record->id_pratica]))
                    ->openUrlInNewTab()
                    ->searchable(),
                TextColumn::make('descrizione'),
                TextColumn::make('status_compenso'),
            ])
            ->filters([
                /*
                 * Filter::make('data_status')
                 *     ->form([
                 *         DatePicker::make('data_status')
                 *             ->label('Provvigioni maturate fino al')
                 *         //  ->default(now()->subMonth()->endOfMonth()),
                 *     ]),
                 */
                SelectFilter::make('cliente_id')
                    ->label('Filtra per Istituto')
                    ->relationship('cliente', 'name')  // 'cliente' è il nome del metodo nel Model, 'nome_societa' la colonna da visualizzare
                    ->searchable()  // Abilita l'autocomplete (Ajax)
                    ->preload()  // Opzionale: carica i primi risultati all'apertura (utile se i clienti non sono decine di migliaia)
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}"),  // Opzionale: per personalizzare cosa vedi nel dropdown
                SelectFilter::make('stato')
                    ->options([
                        'Inserito' => 'Inserito',
                        'Sospeso' => 'Sospeso',
                        'Proforma' => 'Proforma',
                        'Pagato' => 'Pagato',
                        'Annullato' => 'Annullato',
                        'Escluso' => 'Escluso',
                        'Fatturato' => 'Fatturato',
                        'Stornato' => 'Stornato',
                    ])
                    ->multiple()
                    ->default(['Inserito', 'Sospeso'])
                    ->placeholder('Tutti gli stati'),
                SelectFilter::make('status_compenso')
                    ->label('Stato Compenso')
                    ->multiple()
                    ->options(Compenso::all()->pluck('status_compenso', 'status_compenso')),
                SelectFilter::make('mese_status')
                    ->label('Fino al mese')
                    ->options([
                        '01' => 'Gennaio',
                        '02' => 'Febbraio',
                        '03' => 'Marzo',
                        '04' => 'Aprile',
                        '05' => 'Maggio',
                        '06' => 'Giugno',
                        '07' => 'Luglio',
                        '08' => 'Agosto',
                        '09' => 'Settembre',
                        '10' => 'Ottobre',
                        '11' => 'Novembre',
                        '12' => 'Dicembre',
                    ])
                    // Imposta il mese attuale come default (es. "01", "02", ecc.)
                    ->default(now()->startOfMonth()->subDay(1)->format('m'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        $meseScelto = (int) $data['value'];
                        $annoRiferimento = now()->year;

                        // Data al 1° del mese scelto nell'anno corrente
                        $dataScelta = Carbon::create($annoRiferimento, $meseScelto, 1);

                        // Se la data calcolata è nel futuro, sottraiamo un anno
                        if ($dataScelta->isFuture()) {
                            $dataScelta->subYear();
                        }

                        // Calcoliamo l'inizio del mese successivo
                        $dataLimite = $dataScelta->copy()->endOfMonth();

                        return $query->where('data_status', '<=', $dataLimite);
                    })
                    // Opzionale: mostra chiaramente nel badge quale anno è stato applicato
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['value']))
                            return null;

                        $dataScelta = Carbon::create(now()->year, $data['value'], 1);
                        if ($dataScelta->isFuture())
                            $dataScelta->subYear();

                        return 'Stato fino a fine ' . $dataScelta->translatedFormat('F Y');
                    })
            ])
            ->recordActions([
                Action::make('toggleStatus')
                    ->label('')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($record) {
                        $record->update([
                            'stato' => $record->stato === 'Inserito' ? 'Sospeso' : 'Inserito'
                        ]);
                        Notification::make()
                            ->title('Stato aggiornato con successo')
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record): bool => in_array($record->stato, ['Inserito', 'Sospeso']))
                    ->iconButton()
                    ->color('primary'),
                Action::make('forceStatus')
                    ->label('')
                    ->icon('heroicon-o-arrow-uturn-down')
                    ->action(function ($record) {
                        $record->update([
                            'stato' => $record->stato === null ? 'Inserito' : null
                        ]);
                        Notification::make()
                            ->title('Stato forzato con successo')
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record): bool => in_array($record->stato, ['Inserito', null]))
                    ->iconButton()
                    ->color('success'),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    //   DeleteBulkAction::make(),
                    //   ForceDeleteBulkAction::make(),
                    //   RestoreBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('stato')
                    ->label('Stato Pratica')
                    ->collapsible(),  // SOSTITUISCE le vecchie impostazioni di groupingSettings
                Group::make('istituto_finanziario')
                    ->label('Istituto')
                    ->collapsible(),  // SOSTITUISCE le vecchie impostazioni di groupingSettings
            ])
            ->defaultGroup('istituto_finanziario');

        /*
         * ->recordGroupActions([
         * Action::make('create_proforma')
         * ->label('Emetti proforma')
         * ->icon('heroicon-o-arrow-down-tray')
         * ->action(function (Group $livewire, array $data, $groupKey) {
         *     // $groupKey contiene il valore del raggruppamento
         *     // Esporta solo record di questo gruppo
         * }),
         *  ])
         */
        // Se vuoi che sia raggruppato di default:
        // ->defaultGroup('denominazione_riferimento');
    }

    protected function getTableListeners(): array
    {
        return [
            'table-row-selected' => 'handleRowSelected',
            'table-row-deselected' => 'handleRowDeselected',
        ];
    }

    public function handleRowSelected($recordId): void
    {
        $record = Model::find($recordId);
        if ($record->stato === 'Inserito') {
            $record->update(['stato' => 'Sospeso']);
        }
    }

    public function handleRowDeselected($recordId): void
    {
        $record = Model::find($recordId);
        $record->update(['stato' => 'Inserito']);
    }
}
