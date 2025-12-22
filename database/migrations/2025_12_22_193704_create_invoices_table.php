<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->comment('ID univoco della fattura');
            $table->year('competenza')->default('2025');
            $table->string('clienti_id', 36)->nullable()->comment('Riferimento al cliente (se fattura attiva)');
            $table->string('fornitore_piva', 255)->nullable()->comment('Partita IVA del fornitore (per fatture passive)');
            $table->string('fornitore', 255)->nullable()->comment('Ragione sociale del fornitore (per fatture passive)');
            $table->string('cliente_piva', 255)->nullable()->comment('Partita IVA del cliente (per fatture attive)');
            $table->string('cliente', 255)->nullable()->comment('Ragione sociale del cliente (per fatture attive)');
            $table->string('invoice_number')->comment('Numero fattura');
            $table->dateTime('invoice_date')->comment('Data di emissione fattura');
            $table->decimal('total_amount', 10, 2)->comment('Importo totale della fattura (al netto di IVA)');
            $table->decimal('delta', 15, 2)->nullable()->comment('Eventuale scostamento/variazione');
            $table->dateTime('sended_at')->nullable()->comment('Data di primo invio al cliente');
            $table->dateTime('sended2_at')->nullable()->comment('Data di secondo invio (sollecito)');
            $table->decimal('tax_amount', 10, 2)->comment('Importo IVA');
            $table->decimal('importo_iva', 10, 2)->nullable();
            $table->decimal('importo_totale_fornitore', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR')->comment('Valuta (es. EUR)');
            $table->string('payment_method', 255)->nullable()->comment('Modalità di pagamento');
            $table->string('status', 255)->default('imported')->comment('Stato della fattura (es. imported, sent, paid)');
            $table->date('paid_at')->nullable()->comment('Data di pagamento');
            $table->boolean('isreconiled')->default(false)->comment('Indica se la fattura è stata riconciliata (1) o meno (0)');
            $table->boolean('is_notenasarco')->default(false);
            $table->text('xml_data')->nullable()->comment('Dati XML della fattura elettronica (se presente)');
            $table->string('coge', 255)->nullable()->comment('Codice contabile COGE');
            $table->timestamps();
            $table->softDeletes()->comment('Timestamp di cancellazione (soft delete)');

            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->comment('fatture');

            // Indexes
            $table->index('clienti_id');
            $table->index('fornitore_piva');
            $table->index('invoice_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
