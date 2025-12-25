<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('isreconiled')
                    ->label('Riconciliata')
                    ->boolean()
                    ->sortable(),
                        TextColumn::make('fornitore')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Importo')
                    ->money('EUR')
                    ->alignEnd()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('invoice_date')
                  ->label('Del')
                    ->date()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('invoice_number')
                    ->searchable(),
                TextColumn::make('delta')
                    ->money('EUR')
                    ->sortable(),
                ToggleColumn::make('is_notenasarco')
                    ->label('Non Enasarco')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_notenasarco')
                    ->label('Enasarco')
                    ->options([
                        '1' => 'No',
                        '0' => 'Si',
                    ])
                    ->default('0'),
                SelectFilter::make('isreconiled')
                    ->label('Riconciliazione')
                    ->options([
                        '1' => 'Riconciliata',
                        '0' => 'Da Riconciliare',
                    ]) ->default('0'),
            ])
     //       ->recordUrl(InvoiceResource::getUrl('edit', ['record' => $record])
            ->recordActions([
              //  TableViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                  //  DeleteBulkAction::make(),
                ]),
            ]);
    }
}
