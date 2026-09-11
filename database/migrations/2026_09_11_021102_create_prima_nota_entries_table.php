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

            // Relazione polimorfica: crea 'record_type' e 'record_id'
            $table->nullableMorphs('record');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prima_nota_entries');
    }
};
