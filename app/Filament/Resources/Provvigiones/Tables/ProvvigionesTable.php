<?php

namespace App\Filament\Resources\Provvigiones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;

use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Sum;

class ProvvigionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(\App\Models\Provvigione::query()
              ->where('entrata_uscita', 'Uscita')
              ->where(function($query) {
                  $query->where('stato', 'Inserito')
                        ->orWhere('stato', 'Sospeso');
              })
            )
            ->columns([
                TextColumn::make('segnalatore')
                  ->label('Produttore')
                  ->sortable()
                    ->searchable(),
                TextColumn::make('importo')
                 ->label('Provvigione')
                  ->money('EUR') // Forza Euro e formato italiano
                  ->alignEnd()
                  ->summarize(Sum::make()->label('Totale: '))
                    ->sortable(),

                TextColumn::make('stato')
                ->badge()
                  ->sortable()
                    ->searchable(),
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
                    ->searchable(),

            ])
            ->filters([
                Filter::make('stato')
                    ->form([
                        Forms\Components\Select::make('stato')
                            ->label('Stato')
                            ->options([
                                'Inserito' => 'Inserito',
                                'Sospeso' => 'Sospeso',
                            ])
                            ->placeholder('Tutti gli stati'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['stato'],
                                fn (Builder $query, $stato): Builder => $query->where('stato', $stato),
                            );
                    }),
                Filter::make('data_status')
                    ->form([
                        Forms\Components\DatePicker::make('data_limite')
                            ->label('Provvigioni maturate fino al')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['data_limite'],
                                fn (Builder $query, $date): Builder => $query->whereDate('data_status', '<=', $date),
                            );
                    }),

            ])
            ->recordUrl(
                fn ($record) => route('filament.admin.resources.provvigiones.toggle-status', $record)
            )
            ->recordActions([
                ViewAction::make(),
            ])
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
                ->collapsible(), // SOSTITUISCE le vecchie impostazioni di groupingSettings

                 Group::make('segnalatore')
                ->label('Produttore')
                ->collapsible(), // SOSTITUISCE le vecchie impostazioni di groupingSettings
           ])
        // Se vuoi che sia raggruppato di default:
        ->defaultGroup('segnalatore')

            ;
    }
}
