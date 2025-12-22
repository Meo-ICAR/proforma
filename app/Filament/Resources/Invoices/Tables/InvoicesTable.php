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
                TextColumn::make('competenza'),
                TextColumn::make('clienti_id')
                    ->searchable(),
                TextColumn::make('fornitore_piva')
                    ->searchable(),
                TextColumn::make('fornitore')
                    ->searchable(),
                TextColumn::make('cliente_piva')
                    ->searchable(),
                TextColumn::make('cliente')
                    ->searchable(),
                TextColumn::make('invoice_number')
                    ->searchable(),
                TextColumn::make('invoice_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delta')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sended_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sended2_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('importo_iva')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('importo_totale_fornitore')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('paid_at')
                    ->date()
                    ->sortable(),
                IconColumn::make('isreconiled')
                    ->boolean(),
                IconColumn::make('is_notenasarco')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('coge')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
