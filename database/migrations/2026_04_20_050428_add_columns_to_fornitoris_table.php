<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::connection($this->connection)->table('fornitoris', function (Blueprint $table) {
            $table->string('pec', 255)->nullable()->comment('Indirizzo Posta Elettronica Certificata')->after('id');
            $table->string('description', 255)->nullable()->comment('Descrizione')->after('pec');

            $table->enum('supervisor_type', ['no', 'si', 'filiale'])
                ->default('no')
                ->comment('Se supervisore indicare e specificare se di filiale')
                ->after('description');

            $table->string('oam', 30)->nullable()->comment('Oam')->after('supervisor_type');
            $table->date('oam_at')->nullable()->comment('Data iscrizione OAM')->after('oam');
            $table->string('oam_name', 255)->nullable()->comment('Denominazione sociale registrata in OAM')->after('oam_at');
            $table->string('numero_iscrizione_rui', 50)->nullable()->comment('Numero iscrizione OAM')->after('oam_name');

            $table->string('ivass', 30)->nullable()->comment('Codice di iscrizione IVASS')->after('numero_iscrizione_rui');
            $table->date('ivass_at')->nullable()->comment('Data iscrizione IVASS')->after('ivass');
            $table->string('ivass_name', 255)->nullable()->comment('Denominazione IVASS')->after('ivass_at');
            $table->enum('ivass_section', ['A', 'B', 'C', 'D', 'E'])->nullable()->comment('Sezione IVASS')->after('ivass_name');

            $table->date('stipulated_at')->nullable()->comment('Data stipula contratto collaborazione')->after('ivass_section');
            $table->date('dismissed_at')->nullable()->comment('Data cessazione rapporto')->after('stipulated_at');

            $table->string('type', 30)->nullable()->comment('Agente / Mediatore / Consulente / Call Center')->after('dismissed_at');

            $table->boolean('is_active')->default(true)->comment('Indica se agente è attualmente convenzionato')->after('type');
            $table->boolean('is_art108')->default(false)->comment('Esente art. 108 - ex art. 128-novies TUB')->after('is_active');

            $table->unsignedInteger('company_branch_id')->nullable()->comment('Filiale di riferimento')->after('is_art108');
            $table->unsignedInteger('coordinated_type')->nullable()->comment('ID del dipendente coordinatore')->after('company_branch_id');
            $table->unsignedInteger('coordinated_id')->nullable()->comment('ID dell\'agente coordinatore')->after('coordinated_type');
            $table->unsignedBigInteger('user_id')->nullable()->comment('ID dell\'utente collegato')->after('coordinated_id');

            $table->date('oam_dismissed_at')->nullable()->comment('Data revoca OAM')->after('user_id');
            $table->decimal('welcome_bonus', 10, 2)->nullable()->comment('Premio benvenuto')->after('oam_dismissed_at');
            $table->string('campagna', 255)->nullable()->comment('Codice campagna')->after('welcome_bonus');
            $table->date('available_at')->nullable()->comment('Data disponibilità agente')->after('campagna');
            $table->decimal('budget', 10, 2)->nullable()->comment('Budget agente')->after('available_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table('fornitoris', function (Blueprint $table) {
            $table->dropColumn([
                'pec', 'description', 'supervisor_type', 'oam', 'oam_at', 'oam_name',
                'numero_iscrizione_rui', 'ivass', 'ivass_at', 'ivass_name', 'ivass_section',
                'stipulated_at', 'dismissed_at', 'type', 'is_active', 'is_art108',
                'company_branch_id', 'coordinated_type', 'coordinated_id', 'user_id',
                'oam_dismissed_at', 'welcome_bonus', 'campagna', 'available_at', 'budget'
            ]);
        });
    }
};
