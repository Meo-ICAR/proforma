<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('fornitore')
                 ->sortable()
                    ->searchable(),
                TextColumn::make('invoice_date')
                    ->date()
                    ->sortable()
                     ->searchable(),
           TextColumn::make('total_amount')
                    ->money('EUR')
                    ->alignEnd()
                     ->searchable()
                    ->sortable(),
  TextColumn::make('status')
  ->badge()
                    ->searchable(),

                       TextColumn::make('invoice_number')
                    ->searchable(),

                TextColumn::make('delta')
                    ->money('EUR'),


                IconColumn::make('isreconiled')
                    ->boolean()
                      ->sortable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
               // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                  //  DeleteBulkAction::make(),
                ]),
            ]);
    }
}
