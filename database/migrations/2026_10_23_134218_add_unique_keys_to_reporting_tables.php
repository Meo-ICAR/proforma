<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chiavi uniche delle tabelle di riepilogo: i comandi di ricalcolo aggiornano le righe sulla chiave invece di svuotare la tabella.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vcoge', function (Blueprint $table) {
            $table->unique('mese', 'vcoge_mese_unique');
        });

        Schema::table('venasarcotrimestre', function (Blueprint $table) {
            $table->unique(['competenza', 'Trimestre', 'produttore', 'enasarco'], 'venasarcotrimestre_chiave_unique');
        });

        Schema::table('venasarcotot', function (Blueprint $table) {
            $table->unique(['competenza', 'produttore', 'enasarco'], 'venasarcotot_chiave_unique');
        });
    }

    public function down(): void
    {
        Schema::table('vcoge', function (Blueprint $table) {
            $table->dropUnique('vcoge_mese_unique');
        });

        Schema::table('venasarcotrimestre', function (Blueprint $table) {
            $table->dropUnique('venasarcotrimestre_chiave_unique');
        });

        Schema::table('venasarcotot', function (Blueprint $table) {
            $table->dropUnique('venasarcotot_chiave_unique');
        });
    }
};
