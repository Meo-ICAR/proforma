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
        Schema::create('clients', function (Blueprint $table) {
            $table->id()->comment('ID intero autoincrementante');
            $table->char('company_id', 36)->nullable();
            $table->boolean('is_person')->default(true)->comment('Persona fisica (true) o giuridica (false)');
            $table->string('name')->comment('Cognome (se persona fisica) o Ragione Sociale (se giuridica)');
            $table->string('first_name')->nullable()->comment('Nome persona fisica');
            $table->string('tax_code', 16)->nullable()->comment('Codice Fiscale o Partita IVA del cliente');
            $table->string('vat_number', 20)->nullable();
            $table->string('email')->nullable()->comment('Email di contatto principale');
            $table->string('phone', 50)->nullable()->comment('Recapito telefonico');
            $table->boolean('is_pep')->default(false)->comment('Persona Politicamente Esposta');
            $table->unsignedBigInteger('client_type_id')->nullable()->comment('Classificazione cliente');
            $table->boolean('is_sanctioned')->default(false)->comment('Presente in liste antiterrorismo/blacklists');
            $table->boolean('is_remote_interaction')->default(false)->comment('Operatività a distanza = Rischio più alto');
            $table->timestamp('general_consent_at')->nullable()->comment('Consenso generale al trattamento base');
            $table->timestamp('privacy_policy_read_at')->nullable()->comment('Data presa visione informativa Art.13');
            $table->timestamp('consent_special_categories_at')->nullable()->comment('Consenso dati sanitari/giudiziari per polizze/CQS');
            $table->timestamp('consent_sic_at')->nullable()->comment('Consenso interrogazione CRIF/CTC/Experian');
            $table->timestamp('consent_marketing_at')->nullable()->comment('Consenso comunicazioni commerciali e newsletter');
            $table->timestamp('consent_profiling_at')->nullable()->comment('Consenso profilazione abitudini di consumo/spesa');
            $table->string('status')->default('raccolta_dati')->comment('raccolta_dati, valutazione_aml, approvata, sos_inviata, chiusa');
            $table->boolean('is_company')->default(false)->comment("True se il cliente è un'azienda fornitore");
            $table->boolean('is_lead')->default(false)->comment('True se è un lead non ancora convertito');
            $table->unsignedBigInteger('leadsource_id')->nullable()->comment('ID del client che ha fornito il lead');
            $table->timestamp('acquired_at')->nullable()->comment('Data di acquisizione del contatto');
            $table->string('contoCOGE')->nullable()->comment('Conto COGE');
            $table->timestamp('created_at')->useCurrent()->comment('Data acquisizione cliente');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('Ultima modifica anagrafica');
            $table->boolean('privacy_consent')->default(false)->comment('Consenso privacy del cliente');
            $table->boolean('is_client')->default(true)->comment('contraente contratto');
            $table->text('subfornitori')->nullable()->comment('Subfornitori da comunicare per gradimento');
            $table->boolean('is_requiredApprovation')->default(false)->comment('Da far approvare per gradimento');
            $table->boolean('is_approved')->default(true)->comment('Approvata per gradimento');
            $table->boolean('is_anonymous')->default(false)->comment('Cliente anonimo (non comunicabile)');
            $table->timestamp('blacklist_at')->nullable()->comment('Data inserimento in blacklist');
            $table->string('blacklisted_by')->nullable()->comment("ID dell'utente che ha inserito in blacklist (senza link esterni)");
            $table->decimal('salary', 10, 2)->nullable()->comment('Retribuzione annuale del cliente');
            $table->decimal('salary_quote', 10, 2)->nullable()->comment('Quota retribuzione per calcoli finanziari');
            $table->boolean('is_art108')->default(false)->comment('Esente art. 108 - ex art. 128-novies TUB');

            // Indexes
            $table->index(['company_id']);
            $table->index(['client_type_id']);
            $table->index(['leadsource_id']);
            $table->index(['blacklist_at']);
            $table->index(['is_anonymous']);
            $table->index(['is_approved']);

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('client_type_id')->references('id')->on('client_types');
            $table->foreign('leadsource_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
