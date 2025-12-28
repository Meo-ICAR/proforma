<?php

namespace App\Filament\Resources\Proformas\Tables;

use App\Models\Proforma;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;  // ← Import corretto

class ProformasTable
{
  public static function configure(Table $table): Table
  {
    return $table
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
          ->money('EUR')  // Forza Euro e formato italiano
          ->alignEnd()
          ->sortable(),
        TextColumn::make('contributo')
          ->money('EUR')  // Forza Euro e formato italiano
          ->alignEnd()
          ->sortable(),
        TextColumn::make('anticipo')
          ->money('EUR')  // Forza Euro e formato italiano
          ->alignEnd()
          ->sortable(),
        TextColumn::make('updated_at')
          ->label('Modificato')
          ->date()
          ->sortable(),
        TextColumn::make('sended_at')
          ->label('Inviato')
          ->date()
          ->sortable(),
        TextColumn::make('paid_at')
          ->label('Pagato')
          ->date()
          ->sortable(),
        TextColumn::make('delta')
          ->money('EUR')  // Forza Euro e formato italiano
          ->alignEnd()
          ->sortable(),
      ])
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
      ])
      ->recordActions([
        EditAction::make()->label(false),
      ])
      ->toolbarActions([
        BulkAction::make('Invia')
          ->label('Invia email Proforma')
          ->color('primary')
          ->requiresConfirmation()
          ->accessSelectedRecords()
          ->action(function (Collection $records) {
            // Process each record with a visible loop
            $records->each(function ($record) {
              $record->sendEmail();
            });

            // Show success notification with count
            Notification::make()
              ->title(count($records) . '  proforma inviati')
              ->success()
              ->send();
          }),
        // ->iconButton()
        BulkAction::make('test')
          ->label('Test invio email a se stessi')
          ->color('blue')
          ->requiresConfirmation()
          ->accessSelectedRecords()
          ->action(function (Collection $records) {
            // Process each record with a visible loop
            $records->each(function ($record) {
              $record->testEmail();
            });

            // Show success notification with count
            Notification::make()
              ->title(count($records) . '  proforma modificati')
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
              ]);
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
