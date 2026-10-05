<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prima_nota_configs', function (Blueprint $table) {
            $table->id();
            $table->string('event_label');          // Es. "Anticipo Agente", "Quota Enasarco"
            $table->string('model_type');           // Es. "App\Models\Contratto"
            $table->string('value_field');          // Es. "importo_anticipo"
            $table->string('date_field')->nullable(); // Es. "data_pagamento_anticipo" (opzionale)
            $table->string('conto_dare');           // Codice o nome conto Dare
            $table->string('conto_avere');          // Codice o nome conto Avere
            $table->date('effective_from')->nullable(); // Data attivazione: ignora record/eventi antecedenti
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prima_nota_configs');
    }
};
