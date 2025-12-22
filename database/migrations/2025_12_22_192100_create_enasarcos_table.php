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
    Schema::create('enasarcos', function (Blueprint $table) {
        $table->id()->comment('ID univoco');
        $table->year('competenza')->default('2025')->comment('Anno di competenza');
        $table->enum('enasarco', ['monomandatario', 'plurimandatario', 'societa', 'no'])
              ->nullable()
              ->comment('Tipo di mandato ENASARCO');
        $table->decimal('minimo', 10, 2)->nullable()->comment('Minimo imponibile');
        $table->decimal('massimo', 10, 2)->nullable()->comment('Massimo imponibile');
        $table->decimal('minimale', 10, 2)->nullable()->comment('Contributo minimale');
        $table->decimal('massimale', 10, 2)->nullable()->comment('Contributo massimale');
        $table->decimal('aliquota_soc', 5, 2)->nullable()->comment('Aliquota a carico società');
        $table->decimal('aliquota_agente', 5, 2)->nullable()->comment('Aliquota a carico agente');
        $table->timestamps();

        // Add index
        $table->index(['enasarco', 'competenza'], 'enasarco_enasarco_competenza_index');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enasarcos');
    }
};
