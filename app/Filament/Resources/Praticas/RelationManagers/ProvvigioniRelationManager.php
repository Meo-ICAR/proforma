<?php

namespace App\Filament\Resources\Praticas\RelationManagers;

use Filament\Actions\EditAction;
//use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ProvvigioniRelationManager extends RelationManager
{
    protected static string $relationship = 'provvigioni';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('segnalatore'),
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
            ->recordTitleAttribute('Provvigioni associate alla pratica')
            ->columns([
                 TextColumn::make('entrata_uscita'),
                  TextColumn::make('segnalatore'),
                        TextColumn::make('importo')
                        ->money('EUR')
                        ->alignEnd(),
                TextColumn::make('descrizione'),
                 TextColumn::make('quota')
                 ->label('Storno')
                 ->money('EUR')
                 ->alignEnd(),
                  //  ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
              //  CreateAction::make(),
              //   AssociateAction::make(),
            ])
            ->recordActions([
             //   ViewAction::make(),
                Action::make('storna')
                    ->label('Storna')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (\App\Models\Provvigione $record): bool =>
                        $record->entrata_uscita === 'Entrata' &&
                        (!isset($record->quota) )
                    )
                    ->form([
                        TextInput::make('quota')
                            ->label('Importo Storno')
                            ->numeric()
                            ->required()
                            ->maxValue(fn ($record) => $record->importo)
                            ->step(0.01)
                            ->prefix('€')
                    ])
                    ->action(function (array $data, \App\Models\Provvigione $record): void {
                        $provvigioneattiva = $record->importo;
                        $quotaPercent = - $data['quota'] / $provvigioneattiva;

                        // Update the current record
                        $record->update([
                            'quota' => $data['quota'],
                        ]);

                        // Get all related 'Uscita' provvigioni for the same pratica that are not 'Annullato'
                        $relatedUscite = \App\Models\Provvigione::where('id_pratica', $record->id_pratica)
                            ->where('entrata_uscita', 'Uscita')
                            ->where('stato', '!=', 'Annullato')
                            ->get();

                        // Update each related 'Uscita' record
                        foreach ($relatedUscite as $uscita) {
                            $newRecord = $uscita->replicate();
        $newRecord->id = $record->id.'-';
        // 2. Modifica eventuali campi (es. aggiungi "Copia" al titolo)
        $newRecord->status_compenso = 'Pratica stornata';
        $newRecord->importo = $uscita->importo * $quotaPercent;
        $newRecord->decrizione = 'Storno provvigione ' . $record->id;

        // 3. Salva il nuovo record nel database
        $newRecord->save();
                            $uscita->update([
                                'quota' => $uscita->importo * $quotaPercent,
                            ]);
                        }

                        Notification::make()
                            ->title('Provvigione stornata')
                            ->body('Stornate ' . $relatedUscite->count() . ' provvigioni passive')
                            ->success()
                            ->send();
                    }),
              //  EditAction::make(),
              //  DissociateAction::make(),
               // DeleteAction::make(),
            ])
           ;
    }
}
