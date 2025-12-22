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
        Schema::create('coges', function (Blueprint $table) {
            $table->id();
            $table->string('fonte');
            $table->string('entrata_uscita');
            $table->string('conto_dare');
            $table->string('descrizione_dare');
            $table->string('conto_avere');
            $table->string('descrizione_avere');
            $table->string('annotazioni')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coges');
    }
};
