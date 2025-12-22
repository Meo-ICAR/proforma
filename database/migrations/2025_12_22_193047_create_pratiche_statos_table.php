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
        Schema::create('pratiches_statos', function (Blueprint $table) {
            $table->string('stato_pratica', 191)->comment('Stato attuale della pratica');
            $table->integer('isrejected')->default(0)->comment('Flag: 1 se stato rifiutato/annullato');
            $table->integer('isworking')->default(0)->comment('Flag: 1 se stato in lavorazione');
            $table->integer('isestingued')->default(0)->comment('Flag: 1 se stato estinto/concluso');

            $table->primary('stato_pratica');

            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        })->comment('Stati possibili delle pratiche e loro flag');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pratiches_statos');
    }
};
