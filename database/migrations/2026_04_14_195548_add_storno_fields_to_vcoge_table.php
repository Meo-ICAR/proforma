<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vcoge', function (Blueprint $table) {
            $table->decimal('storno_entrata', 10, 2)->default(0)->after('uscita');
            $table->decimal('storno_uscita', 10, 2)->default(0)->after('storno_entrata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vcoge', function (Blueprint $table) {
            $table->dropColumn(['storno_entrata', 'storno_uscita']);
        });
    }
};
