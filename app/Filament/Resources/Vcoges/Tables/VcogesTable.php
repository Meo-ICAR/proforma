<?php

namespace App\Filament\Resources\Vcoges\Tables;

use App\Models\Vcoge;  // Make sure this is correctly cased
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class VcogesTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mese')
                    ->searchable(),
                TextColumn::make('entrata')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->label('Entrata')
                    ->sortable(),
                TextColumn::make('uscita')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make()->money('EUR')->label(''))
                    ->label('Uscita')
                    ->sortable(),
                TextColumn::make('saldo')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
            ])
            ->defaultSort('mese', 'desc')
            ->filters([
                SelectFilter::make('mese')
                    ->label('Mesi')
                    ->options(fn() => Vcoge::getDistinctMonths())
                    ->multiple()
                // In Filament 4.x, il valore di default viene applicato automaticamente
                // se non diversamente specificato.
            ])
            ->recordActions([
                Action::make('invia')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->label('Invia in Contabilita la primanota ')
                    ->action(function (Vcoge $record) {
                        try {
                            $exitCode = \Illuminate\Support\Facades\Artisan::call('coge:sync-monthly', [
                                '--month' => $record->mese
                            ]);

                            if ($exitCode === 0) {
                                Notification::make()
                                    ->title('Invio Completato')
                                    ->body('I dati sono stati inviati correttamente.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Errore Invio Dati')
                                    ->body('Si è verificato un errore durante l\'invio. Controlla i log per i dettagli.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Log::error("Eccezione durante il richiamo del comando coge:sync-monthly: " . $e->getMessage());
                            Notification::make()
                                ->title('Errore Inaspettato')
                                ->body('Si è verificato un errore imprevisto.')
                                ->danger()
                                ->send();
                        }
                    })
            ])
            ->toolbarActions([]);
    }

    }
