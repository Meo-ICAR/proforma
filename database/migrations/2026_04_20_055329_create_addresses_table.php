<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id()->comment('ID intero autoincrementante');
            $table->string('addressable_type')->comment('Classe del Modello collegato (es. App\Models\Client)');
            $table->string('addressable_id')->comment('ID del Modello (VARCHAR 36 per supportare sia UUID che Integer)');
            $table->string('name')->nullable()->comment('Descrizione');
            $table->string('numero')->nullable()->comment('Numero civico o identificativo indirizzo');
            $table->string('street')->nullable()->comment('Via e numero civico');
            $table->string('city')->nullable()->comment('Città o Comune');
            $table->string('zip_code', 20)->nullable()->comment('CAP (Codice di Avviamento Postale)');
            $table->unsignedBigInteger('address_type_id')->nullable()->comment('Relazione con tipologia indirizzo');
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Data inserimento indirizzo');
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Data ultimo aggiornamento');
            $table->timestamp('deleted_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('Data cancellazione');

            $table->index(['addressable_type', 'addressable_id']);
            $table->foreign('address_type_id')->references('id')->on('address_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
