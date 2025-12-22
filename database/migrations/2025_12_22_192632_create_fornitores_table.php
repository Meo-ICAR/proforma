<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/[timestamp]_create_fornitoris_table.php

public function up()
{
    Schema::create('fornitoris', function (Blueprint $table) {
        $table->char('id', 36)->primary()->comment('ID univoco del fornitore/agente');
        $table->string('codice')->nullable()->comment('Codice identificativo del fornitore');
        $table->string('coge')->nullable()->comment('Codice contabile COGE');
        $table->string('name')->nullable()->comment('Nome completo (ragione sociale)');
        $table->string('nome')->nullable()->comment('Nome del referente');
        $table->date('natoil')->nullable()->comment('Data di nascita');
        $table->string('indirizzo')->nullable()->comment('Indirizzo');
        $table->string('comune')->nullable()->comment('Comune di residenza');
        $table->string('cap', 5)->nullable()->comment('Codice di avviamento postale');
        $table->string('prov', 2)->nullable()->comment('Provincia');
        $table->string('tel')->nullable()->comment('Numero di telefono');
        $table->string('coordinatore')->nullable()->comment('Nome del coordinatore di riferimento');
        $table->string('piva', 20)->nullable()->unique()->comment('Partita IVA');
        $table->char('cf', 16)->nullable()->comment('Codice fiscale');
        $table->string('nomecoge')->nullable()->comment('Nome per la contabilità');
        $table->string('nomefattura')->nullable()->comment('Nome da utilizzare in fattura');
        $table->string('email')->nullable()->comment('Indirizzo email');
        $table->decimal('anticipo', 15, 2)->nullable()->comment('Importo dell\'anticipo concesso');
        $table->enum('enasarco', ['no', 'monomandatario', 'plurimandatario', 'societa'])->nullable()->comment('Tipo di mandato ENASARCO');
        $table->decimal('anticipo_residuo', 15, 2)->nullable()->comment('Residuo dell\'anticipo da recuperare');
        $table->decimal('contributo', 15, 2)->nullable()->comment('Importo del contributo spese');
        $table->string('contributo_description')->default('Contributo spese')->comment('Descrizione del contributo spese');
        $table->string('anticipo_description')->default('Anticipo attuale')->comment('Descrizione dell\'anticipo');
        $table->boolean('issubfornitore')->default(false)->comment('Indica se è un subfornitore (1) o meno (0)');
        $table->string('operatore')->nullable()->comment('Utente che ha creato/modificato il record');
        $table->boolean('iscollaboratore')->nullable()->comment('Indica se è un collaboratore (1) o meno (0)');
        $table->boolean('isdipendente')->nullable()->comment('Indica se è un dipendente (1) o meno (0)');
        $table->string('regione')->nullable()->comment('Regione di appartenenza');
        $table->string('citta')->nullable()->comment('Città di riferimento');
        $table->softDeletes()->comment('Timestamp di cancellazione (soft delete)');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornitores');
    }
};
