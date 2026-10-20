<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riferimento alle pratiche del database di Proforma (`pratiches.id`), impostato dall'import dei compensi di Mediafacile
     * quando la pratica c'è anche lì. Il legame comune tra i database resta il codice della pratica (`id_pratica`).
     */
    public function up(): void
    {
        if (! Schema::hasTable('provvigioni')) {
            return;
        }

        if (Schema::hasColumn('provvigioni', 'pratica_id')) {
            return;
        }

        Schema::table('provvigioni', function (Blueprint $table) {
            $table->unsignedBigInteger('pratica_id')->nullable()->after('id_pratica')->index()->comment('pratiches.id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('provvigioni', 'pratica_id')) {
            return;
        }

        Schema::table('provvigioni', function (Blueprint $table) {
            $table->dropIndex(['pratica_id']);
            $table->dropColumn('pratica_id');
        });
    }
};
