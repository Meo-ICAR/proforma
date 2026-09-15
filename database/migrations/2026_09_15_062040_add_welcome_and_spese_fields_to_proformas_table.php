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
        Schema::table('proformas', function (Blueprint $table) {
            $table->decimal('welcome', 15, 2)->nullable()->after('contributo_descrizione')
                ->comment('Importo del welcome bonus');
            $table->string('welcome_description', 255)->nullable()->after('welcome')
                ->comment('Causale del welcome bonus');
            $table->decimal('spese', 15, 2)->nullable()->after('welcome_description')
                ->comment('Importo delle spese');
            $table->string('spese_description', 255)->nullable()->after('spese')
                ->comment('Causale delle spese');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropColumn(['welcome', 'welcome_description', 'spese', 'spese_description']);
        });
    }
};
