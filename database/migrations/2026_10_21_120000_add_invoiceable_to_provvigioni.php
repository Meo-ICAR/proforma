<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fattura di vendita abbinata direttamente a una provvigione di tipo Cliente, senza passare per il proforma
 * (quelle di Istituto e Agente arrivano alla fattura attraverso il proforma).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('provvigioni')) {
            return;
        }

        Schema::table('provvigioni', function (Blueprint $table) {
            if (! Schema::hasColumn('provvigioni', 'invoiceable_type')) {
                $table->string('invoiceable_type')->nullable()->comment('Tipo della fattura abbinata (SalesInvoice)');
            }
            if (! Schema::hasColumn('provvigioni', 'invoiceable_id')) {
                $table->unsignedBigInteger('invoiceable_id')->nullable()->comment('Fattura abbinata direttamente, senza proforma');
            }
        });

        if (! collect(Schema::getIndexes('provvigioni'))->contains(fn (array $index) => $index['name'] === 'provvigioni_invoiceable_index')) {
            Schema::table('provvigioni', fn (Blueprint $table) => $table->index(['invoiceable_type', 'invoiceable_id'], 'provvigioni_invoiceable_index'));
        }
    }

    public function down(): void
    {
        Schema::table('provvigioni', function (Blueprint $table) {
            $table->dropIndex('provvigioni_invoiceable_index');
            $table->dropColumn(['invoiceable_type', 'invoiceable_id']);
        });
    }
};
