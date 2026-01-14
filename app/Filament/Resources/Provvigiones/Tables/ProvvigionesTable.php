<?php

namespace App\Filament\Resources\Provvigiones\Tables;

use App\Filament\Resources\Praticas\PraticaResource;
use App\Models\Compenso;
use App\Models\Proforma;
use App\Models\Provvigione;
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

class ProvvigionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Provvigione::query()
                ->where('entrata_uscita', 'Uscita')
                ->whereNot('importo', 0)
            //  ->whereNot('annullato', 1))
            )
            ->reorderableColumns()
            ->selectable()
            ->checkIfRecordIsSelectableUsing(
                fn(Model $record): bool => $record->stato === 'Inserito'
            )
            ->headerActions([
                BulkAction::make('emetti')
                    ->label('Emetti Proforma')
                    ->color('success')
                    ->requiresConfirmation()
                    ->accessSelectedRecords()
                    ->action(function (Collection $records) {
                        // Process each record with a visible loop
                        $records->each(function ($record) {
                            $piva = $record->piva;
                            if (($piva > '0')) {
                                $proformaId = Proforma::findOrCreateByPiva($piva, $record->importo);
                                $record->update([
                                    'stato' => 'Proforma',
                                    'proforma_id' => $proformaId
                                ]);
                            } else {
                                Notification::make()
                                    ->title('ATTENZIONE Provvigione senza partita IVA' . $record->id . ' ' . $record->denominazione_riferimento
                                        . ' ' . $record->pratica->cognome_cliente . ' ' . $record->pratica->nome_cliente . ' ' . $record->pratica->id_pratica . ' proforma non emesso')
                                    ->danger()
                                    ->send();
                            }
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
                    })
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
                TextColumn::make('denominazione_riferimento')
                    ->label('Produttore')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('importo')
                    ->label('Provvigione')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label('')->query(fn(Builderq $query) => $query->where('stato', 'Inserito')))
                    ->sortable(),
                IconColumn::make('coordinamento')
                    ->boolean()
                    ->sortable()
                    ->trueIcon(Heroicon::OutlinedCheckBadge)
                    ->falseIcon(Heroicon::OutlinedMinus)
                    ->falseColor('white')
                    ->label('Coord'),
                TextColumn::make('data_status')
                    ->date()
                    ->sortable(),
                TextColumn::make('pratica.cognome_cliente')
                    ->label('Cognome Cliente')
                    ->searchable(),
                TextColumn::make('pratica.nome_cliente')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('istituto_finanziario')
                    ->searchable(),
                TextColumn::make('id_pratica')
                    ->label('Pratica')
                    ->color('info')
                    ->url(fn($record) => PraticaResource::getUrl('view', ['record' => $record->id_pratica]))
                    ->openUrlInNewTab()
                    ->searchable(),
                TextColumn::make('descrizione'),
                TextColumn::make('status_compenso'),
                TextColumn::make('piva'),
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
                SelectFilter::make('coordinamento')
                    ->options([
                        1 => 'Si',
                        0 => 'No',
                    ])
                    ->placeholder('Tutti'),
                SelectFilter::make('status_compenso')
                    ->label('Stato Compenso')
                    ->multiple()
                    ->options(Compenso::all()->pluck('status_compenso', 'status_compenso')),
                SelectFilter::make('annullato')
                    ->label('Annullati')
                    ->options([
                        1 => 'Si',
                        0 => 'No',
                    ])
                    ->placeholder('Tutti'),
                Filter::make('mese_riferimento')
                    ->form([
                        DatePicker::make('mese')
                            ->label('Seleziona Mese')
                            ->native(false)
                            ->displayFormat('m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $date = $data['mese'] ?? now()->subDays(20);
                        return $query->when(
                            $data['mese'],
                            fn(Builder $query, $date): Builder => $query
                                ->whereMonth('data_status', \Carbon\Carbon::parse($date)->month)
                                ->whereYear('data_status', \Carbon\Carbon::parse($date)->year),
                        );
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
                Group::make('denominazione_riferimento')
                    ->label('Produttore')
                    ->collapsible(),  // SOSTITUISCE le vecchie impostazioni di groupingSettings
            ])
            ->defaultGroup('denominazione_riferimento');

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
