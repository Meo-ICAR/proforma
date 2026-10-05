<?php

namespace App\Filament\Resources\InvoiceIns\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InvoiceInForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tipo_di_documento'),
                TextInput::make('nr_documento'),
                TextInput::make('nr_fatt_acq_registrata'),
                TextInput::make('nr_nota_cr_acq_registrata'),
                DatePicker::make('data_ricezione_fatt'),
                TextInput::make('codice_td'),
                TextInput::make('nr_cliente_fornitore'),
                TextInput::make('nome_fornitore'),
                TextInput::make('partita_iva'),
                TextInput::make('nr_documento_fornitore'),
                TextInput::make('allegato'),
                DatePicker::make('data_documento_fornitore'),
                DatePicker::make('data_primo_pagamento_prev'),
                TextInput::make('imponibile_iva')
                    ->numeric(),
                TextInput::make('importo_iva')
                    ->numeric(),
                TextInput::make('importo_totale_fornitore')
                    ->numeric(),
                TextInput::make('importo_totale_collegato')
                    ->numeric(),
                DateTimePicker::make('data_ora_invio_ricezione'),
                TextInput::make('stato'),
                TextInput::make('id_documento'),
                TextInput::make('id_sdi'),
                TextInput::make('nr_lotto_documento'),
                TextInput::make('nome_file_doc_elettronico'),
                TextInput::make('filtro_carichi'),
                TextInput::make('cdc_codice'),
                TextInput::make('cod_colleg_dimen_2'),
                Toggle::make('allegato_in_file_xml'),
                Textarea::make('note_1')
                    ->columnSpanFull(),
                Textarea::make('note_2')
                    ->columnSpanFull(),
            ]);
    }
}
