<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/[timestamp]_create_invoice_ins_table.php

public function up()
{
    Schema::create('invoiceins', function (Blueprint $table) {
        $table->id()->comment('ID univoco del record di importazione');
        $table->string('tipo_di_documento')->nullable()->comment('Tipologia del documento (es. Fattura, Nota di credito)');
        $table->string('nr_documento')->nullable()->comment('Numero progressivo del documento');
        $table->string('nr_fatt_acq_registrata')->nullable()->comment('Numero di fattura acquisto registrata');
        $table->string('nr_nota_cr_acq_registrata')->nullable()->comment('Numero di nota di credito acquisto registrata');
        $table->date('data_ricezione_fatt')->nullable()->comment('Data di ricezione della fattura');
        $table->string('codice_td')->nullable()->comment('Codice tipo documento');
        $table->string('nr_cliente_fornitore')->nullable()->comment('Codice identificativo del fornitore');
        $table->string('nome_fornitore')->nullable()->comment('Ragione sociale del fornitore');
        $table->string('partita_iva')->nullable()->comment('Partita IVA del fornitore');
        $table->string('nr_documento_fornitore')->nullable()->comment('Numero del documento del fornitore');
        $table->string('allegato')->nullable()->comment('Allegati al documento');
        $table->date('data_documento_fornitore')->nullable()->comment('Data del documento del fornitore');
        $table->date('data_primo_pagamento_prev')->nullable()->comment('Data prevista per il primo pagamento');
        $table->decimal('imponibile_iva', 15, 2)->nullable()->comment('Imponibile IVA');
        $table->decimal('importo_iva', 15, 2)->nullable()->comment('Importo IVA');
        $table->decimal('importo_totale_fornitore', 15, 2)->nullable()->comment('Importo totale della fattura fornitore');
        $table->decimal('importo_totale_collegato', 15, 2)->nullable()->comment('Importo totale collegato');
        $table->dateTime('data_ora_invio_ricezione')->nullable()->comment('Data e ora di invio/ricezione');
        $table->string('stato')->nullable()->comment('Stato del documento');
        $table->string('id_documento')->nullable()->comment('ID univoco del documento');
        $table->string('id_sdi')->nullable()->comment('ID Sistema di Interscambio (per fatture elettroniche)');
        $table->string('nr_lotto_documento')->nullable()->comment('Numero lotto del documento');
        $table->string('nome_file_doc_elettronico')->nullable()->comment('Nome file documento elettronico');
        $table->string('filtro_carichi')->nullable()->comment('Filtro per carichi');
        $table->string('cdc_codice')->nullable()->comment('Codice centro di costo');
        $table->string('cod_colleg_dimen_2')->nullable()->comment('Codice collegamento dimensione 2');
        $table->boolean('allegato_in_file_xml')->nullable()->comment('Indica se è presente un allegato nel file XML (1) o meno (0)');
        $table->text('note_1')->nullable()->comment('Note aggiuntive 1');
        $table->text('note_2')->nullable()->comment('Note aggiuntive 2');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_ins');
    }
};
