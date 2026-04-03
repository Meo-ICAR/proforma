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
        Schema::table('pratiches', function (Blueprint $table) {
            $table->timestamp('upload_at')->default(now())->after('is_notowned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pratiches', function (Blueprint $table) {
            $table->dropColumn('upload_at');
        });
    }
};
