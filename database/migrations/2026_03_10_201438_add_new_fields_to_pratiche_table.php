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
            $table->date('sended_at')->nullable()->after('nrate');
            $table->date('approved_at')->nullable()->after('sended_at');
            $table->date('erogated_at')->nullable()->after('approved_at');
            $table->decimal('amount', 10, 2)->nullable()->after('erogated_at');
            $table->decimal('net', 10, 2)->nullable()->after('amount');
            $table->boolean('is_notowned')->default(false)->after('net');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pratiches', function (Blueprint $table) {
            $table->dropColumn([
                'sended_at',
                'approved_at',
                'erogated_at',
                'amount',
                'net',
                'is_notowned'
            ]);
        });
    }
};
