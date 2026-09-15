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
            $table->renameColumn('event_label', 'name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prima_nota_configs', function (Blueprint $table) {
            $table->renameColumn('name', 'event_label');
        });
    }
};
