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
        Schema::table('proformas', function (Blueprint $table) {
            $table->string('tipo')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('invoiceable_type')->nullable();
            $table->unsignedBigInteger('invoiceable_id')->nullable();
            $table->index(['invoiceable_type', 'invoiceable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proformas', function (Blueprint $table) {
            $table->dropIndex(['invoiceable_type', 'invoiceable_id']);
            $table->dropColumn('invoiceable_type');
            $table->dropColumn('invoiceable_id');
        });
    }
};
