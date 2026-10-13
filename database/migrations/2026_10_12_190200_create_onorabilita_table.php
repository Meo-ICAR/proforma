<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('onorabilita')) {
    Schema::create('onorabilita', function (Blueprint $table) {
                $table->id()->comment('ID intero autoincrementante');
                $table->string('nominativo')->comment('Nominativo della persona verificata');
                $table->date('data_ultimo_carico_pendente')->nullable()->comment('Data dell\'ultimo certificato dei carichi pendenti');
                $table->date('data_ultima_onorabilita')->nullable()->comment('Data dell\'ultima verifica dei requisiti di onorabilità');
                $table->comment('Verifiche dei requisiti di onorabilità delle persone che operano per l\'azienda');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('onorabilita');
    }
};
