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
            $table->boolean('is_positive')->nullable()->after('value_field')
                ->comment('true = solo importi > 0, false = solo importi < 0, null = qualsiasi segno (esclude comunque lo zero)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prima_nota_configs', function (Blueprint $table) {
            $table->dropColumn('is_positive');
        });
    }
};
