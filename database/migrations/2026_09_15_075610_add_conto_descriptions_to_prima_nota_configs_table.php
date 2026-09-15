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
        Schema::table('prima_nota_configs', function (Blueprint $table) {
            $table->string('conto_dare_description', 255)->nullable()->after('conto_dare')
                ->comment('Descrizione del conto Dare per la riga di prima nota generata');
            $table->string('conto_avere_description', 255)->nullable()->after('conto_avere')
                ->comment('Descrizione del conto Avere per la riga di prima nota generata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prima_nota_configs', function (Blueprint $table) {
            $table->dropColumn(['conto_dare_description', 'conto_avere_description']);
        });
    }
};
