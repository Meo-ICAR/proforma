<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prima_nota_entries', function (Blueprint $table) {
            $table->id();
            // Collegamento alla regola di configurazione che ha generato la voce
            $table->foreignId('prima_nota_config_id')
                ->constrained('prima_nota_configs')
                ->cascadeOnDelete();

            $table->date('data');                   // Data di registrazione prima nota
            $table->decimal('importo', 12, 2);      // Importo prelevato
            $table->string('conto_dare');
            $table->string('conto_avere');

            // Relazione polimorfica: 'record_type' e 'record_id'.
            // record_id è string (non i bigint di nullableMorphs) perché Pratica, Provvigione
            // e Fornitore usano chiavi primarie non incrementali (codici pratica, UUID).
            $table->string('record_type')->nullable();
            $table->string('record_id')->nullable();
            $table->index(['record_type', 'record_id']);
            $table->timestamp('synced_at')->nullable();
            $table->text('sync_error')->nullable();

            // Consente di escludere manualmente una voce dall'invio a Business Central.
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prima_nota_entries');
    }
};
