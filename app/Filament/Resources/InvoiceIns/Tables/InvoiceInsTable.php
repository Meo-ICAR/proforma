<?php

namespace App\Filament\Resources\InvoiceIns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoiceInsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo_di_documento')
                    ->searchable(),
                TextColumn::make('nr_documento')
                    ->searchable(),
                TextColumn::make('nr_fatt_acq_registrata')
                    ->searchable(),
                TextColumn::make('nr_nota_cr_acq_registrata')
                    ->searchable(),
                TextColumn::make('data_ricezione_fatt')
                    ->date()
                    ->sortable(),
                TextColumn::make('codice_td')
                    ->searchable(),
                TextColumn::make('nr_cliente_fornitore')
                    ->searchable(),
                TextColumn::make('nome_fornitore')
                    ->searchable(),
                TextColumn::make('partita_iva')
                    ->searchable(),
                TextColumn::make('nr_documento_fornitore')
                    ->searchable(),
                TextColumn::make('allegato')
                    ->searchable(),
                TextColumn::make('data_documento_fornitore')
                    ->date()
                    ->sortable(),
                TextColumn::make('data_primo_pagamento_prev')
                    ->date()
                    ->sortable(),
                TextColumn::make('imponibile_iva')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('importo_iva')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('importo_totale_fornitore')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('importo_totale_collegato')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('data_ora_invio_ricezione')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('stato')
                    ->searchable(),
                TextColumn::make('id_documento')
                    ->searchable(),
                TextColumn::make('id_sdi')
                    ->searchable(),
                TextColumn::make('nr_lotto_documento')
                    ->searchable(),
                TextColumn::make('nome_file_doc_elettronico')
                    ->searchable(),
                TextColumn::make('filtro_carichi')
                    ->searchable(),
                TextColumn::make('cdc_codice')
                    ->searchable(),
                TextColumn::make('cod_colleg_dimen_2')
                    ->searchable(),
                IconColumn::make('allegato_in_file_xml')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
