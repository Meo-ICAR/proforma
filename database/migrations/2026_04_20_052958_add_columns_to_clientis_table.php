<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** La connessione al database da utilizzare. */
    //  protected $connection = 'nome_tua_connessione';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection($this->connection)->table('clientis', function (Blueprint $table) {
            // Campi bancari/identificativi
            $table->string('abi', 30)->nullable()->comment('Abi per banche o numero RUI ISVASS')->after('id');
            $table->string('abi_name', 255)->nullable()->comment('Nome ufficiale banca')->after('abi');
            $table->date('stipulated_at')->nullable()->comment('Data stipula contratto convenzione')->after('abi_name');
            $table->date('dismissed_at')->nullable()->comment('Data cessazione rapporto convenzione')->after('stipulated_at');

            // Tipologia e registri
            $table->string('type', 30)->nullable()->comment('Banca / Assicurazione / Utility')->after('dismissed_at');
            $table->string('oam', 30)->nullable()->comment('Codice di iscrizione OAM')->after('type');
            $table->string('oam_name', 255)->nullable()->comment('Denominazione OAM')->after('oam');
            $table->date('oam_at')->nullable()->comment('Data iscrizione OAM')->after('oam_name');
            $table->string('numero_iscrizione_rui', 50)->nullable()->comment('Numero iscrizione OAM')->after('oam_at');

            $table->string('ivass', 30)->nullable()->comment('Codice di iscrizione IVASS')->after('numero_iscrizione_rui');
            $table->date('ivass_at')->nullable()->comment('Data iscrizione IVASS')->after('ivass');
            $table->string('ivass_name', 255)->nullable()->comment('Denominazione OAM')->after('ivass_at');
            $table->enum('ivass_section', ['A', 'B', 'C', 'D', 'E'])->nullable()->comment('Sezione IVASS')->after('ivass_name');

            // Dati Mandato
            $table->string('mandate_number', 100)->nullable()->comment('Numero di protocollo o identificativo del contratto di mandato')->after('ivass_section');
            $table->date('start_date')->nullable()->comment('Data di decorrenza del mandato')->after('mandate_number');
            $table->date('end_date')->nullable()->comment('Data di scadenza (NULL se a tempo indeterminato)')->after('start_date');
            $table->boolean('is_exclusive')->default(false)->comment("Indica se il mandato prevede l'esclusiva per quella categoria")->after('end_date');
            $table->enum('status', ['ATTIVO', 'SCADUTO', 'RECEDUTO', 'SOSPESO'])->default('ATTIVO')->comment('Stato operativo del mandato')->after('is_exclusive');

            // Note e dettagli operativi
            $table->text('notes')->nullable()->comment('Note su provvigioni particolari o patti specifici')->after('status');
            $table->enum('principal_type', ['--', 'banca', 'agente_assicurativo', 'agente_captive'])->default('banca')->comment('Tipologia del mandante')->after('notes');
            $table->enum('submission_type', ['--', 'accesso portale', 'inoltro', 'entrambi'])->default('accesso portale')->comment('Modalità inoltro pratiche')->after('principal_type');

            $table->boolean('is_reported')->default(false)->comment('Accordi di segnalazione')->after('website');

            $table->string('privacy_contact_email')->nullable()->comment('Email contatto privacy')->after('is_reported');
            $table->string('dpo_email')->nullable()->comment('Email DPO')->after('privacy_contact_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table('clientis', function (Blueprint $table) {
            $table->dropColumn([
                'abi', 'abi_name', 'stipulated_at', 'dismissed_at', 'type', 'oam', 'oam_name',
                'oam_at', 'numero_iscrizione_rui', 'ivass', 'ivass_at', 'ivass_name',
                'ivass_section', 'mandate_number', 'start_date', 'end_date', 'is_exclusive',
                'status', 'notes', 'principal_type', 'submission_type', 'website', 'is_reported'
            ]);
        });
    }
};
