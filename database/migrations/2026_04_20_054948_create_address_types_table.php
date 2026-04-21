<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Specifica la tua connessione se diversa dalla default
    // protected $connection = 'nome_tua_connessione';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->create('address_types', function (Blueprint $table) {
            $table->id()->comment('ID univoco tipo indirizzo');
            $table->string('name', 255)->nullable()->comment('Descrizione');

            // Se non vuoi i campi created_at e updated_at, lasciali commentati
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('address_types');
    }
};
