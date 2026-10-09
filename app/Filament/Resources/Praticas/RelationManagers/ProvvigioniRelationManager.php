<?php

namespace App\Filament\Resources\Praticas\RelationManagers;

use App\Filament\Traits\HasRelationPlanAccess;
use App\Models\Provvigione;
// use Filament\Actions\Action;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProvvigioniRelationManager extends RelationManager
{
    use HasRelationPlanAccess;

    protected static string $relationship = 'provvigioni';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('denominazione_riferimento'),
                TextInput::make('importo'),
                //  ->money('EUR')
                // ->alignEnd()
                TextInput::make('descrizione')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('entrata_uscita'),
                TextEntry::make('segnalatore'),
                TextEntry::make('importo')
                    ->money('EUR')
                    ->alignEnd(),
                TextEntry::make('descrizione'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderableColumns()
            ->recordTitleAttribute('Provvigioni associate alla pratica')
            ->columns([
                TextColumn::make('entrata_uscita')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Entrata' => 'success',
                        'Uscita' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('denominazione_riferimento')
                    ->label('Produttore'),
                TextColumn::make('importo')
                    ->money('EUR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label('Totale')),
                TextColumn::make('descrizione'),
                TextColumn::make('quota')
                    ->label('Storno')
                    ->money('EUR')
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label('Totale')),
                //  ->searchable(),
                TextColumn::make('descrizione'),
                TextColumn::make('status_compenso'),
                TextColumn::make('data_status')
                    ->date(),
                TextColumn::make('stato'),
                TextColumn::make('data_fattura'),
                TextColumn::make('n_fattura'),
                TextColumn::make('id'),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->placeholder('Tutte')
                    ->options([
                        'Istituto' => 'Istituto',
                        'Agente' => 'Agente',
                    ]),
            ])
            ->headerActions([
                //  CreateAction::make(),
                //   AssociateAction::make(),
            ])
            ->recordActions([
                //   ViewAction::make(),
                Action::make('annulla')
                    ->label('Annulla storno')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Provvigione $record): bool => $record->entrata_uscita === 'Uscita' &&
                        isset($record->quota) &&
                        $record->quota > 0 &&
                        ! isset($record->proforma_id))
                    ->action(function (Provvigione $record): void {
                        // La riga di storno passivo è stata creata da 'storna' con id = "{$record->id}-".
                        Provvigione::where('id', $record->id.'-')->delete();

                        $record->update([
                            'quota' => 0,
                        ]);
                    }),
                Action::make('storna')
                    ->label('Storna')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Provvigione $record): bool => ($record->entrata_uscita === 'Entrata') &&
                        ($record->tipo === 'Istituto') &&
                        ($record->importo > 0) &&
                        ($record->quota == 0))
                    ->form([
                        TextInput::make('quota')
                            ->label('Importo Storno')
                            ->numeric()
                            ->required()
                            ->maxValue(fn ($record) => $record->importo)
                            ->step(0.01)
                            ->prefix('€'),
                    ])
                    ->action(function (array $data, Provvigione $record): void {
                        $quota = $data['quota'];
                        $relatedEntrata = $record->replicate();

                        $relatedEntrata->id = $record->id.'-';
                        $relatedEntrata->data_inserimento_compenso = now();
                        $relatedEntrata->data_status = now();
                        $relatedEntrata->data_pagamento = null;
                        $relatedEntrata->erogated_at = now();

                        $relatedEntrata->descrizione = 'Storno provvigione';
                        $relatedEntrata->status_compenso = 'Pratica stornata';

                        $relatedEntrata->stato = 'Inserito';
                        $relatedEntrata->n_fattura = null;
                        $relatedEntrata->data_fattura = null;
                        $relatedEntrata->importo = -$quota;
                        $relatedEntrata->status_pagamento = 'Inserito';
                        $relatedEntrata->proforma_id = null;

                        $relatedEntrata->save();

                        $provvigioneattiva = $record->importo;
                        $quotaPercent = -$quota / $provvigioneattiva;

                        // Update the current record
                        $record->update([
                            'quota' => $data['quota'],
                        ]);

                        // Get all related 'Uscita' provvigioni for the same pratica that are not 'Annullato'
                        $relatedUscite = Provvigione::where('id_pratica', $record->id_pratica)
                            ->where('entrata_uscita', 'Uscita')
                            ->where('stato', '!=', 'Annullato')
                            ->where('iscliente', '!=', true)
                            ->where('id', 'not like', '%-')
                            ->get();

                        // Update each related 'Uscita' record
                        foreach ($relatedUscite as $uscita) {
                            $stornoUscita = $uscita->importo * $quotaPercent;
                            $newRecord = Provvigione::find($uscita->id.'-');

                            if ($newRecord) {
                                // Storno già presente (es. secondo storno sulla stessa pratica): si cumula.
                                $newRecord->importo += $stornoUscita;
                                $newRecord->save();
                                $uscita->update(['quota' => $uscita->quota + $stornoUscita]);

                                continue;
                            }

                            $newRecord = $uscita->replicate();
                            $newRecord->id = $uscita->id.'-';
                            // 2. Modifica eventuali campi (es. aggiungi "Copia" al titolo)
                            $newRecord->status_compenso = 'Pratica stornata';
                            $newRecord->importo = $stornoUscita;
                            $newRecord->descrizione = 'Storno provvigione ';
                            $newRecord->data_inserimento_compenso = now();
                            $newRecord->data_status = now();
                            $newRecord->erogated_at = now();
                            $newRecord->data_pagamento = null;
                            $newRecord->stato = 'Inserito';
                            $newRecord->n_fattura = null;
                            $newRecord->data_fattura = null;
                            $newRecord->status_pagamento = 'Inserito';
                            $newRecord->proforma_id = null;

                            // 3. Salva il nuovo record nel database
                            $newRecord->save();
                            $uscita->update([
                                'quota' => $stornoUscita,
                            ]);
                        }

                        Notification::make()
                            ->title('Provvigione stornata')
                            ->body('Stornate '.$relatedUscite->count().' provvigioni passive')
                            ->success()
                            ->send();
                    }),
                //  EditAction::make(),
                //  DissociateAction::make(),
                // DeleteAction::make(),
            ], position: RecordActionsPosition::BeforeColumns);
    }
}
