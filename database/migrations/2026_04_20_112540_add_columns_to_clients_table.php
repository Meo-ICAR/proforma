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
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('is_consultant_gdpr')->default(false)->comment('Consulente ai fini GDPR');
            $table->string('privacy_contact_email')->nullable()->comment('Email contatto privacy');
            $table->string('dpo_email')->nullable()->comment('Email DPO');
            $table->boolean('is_iso27001_certified')->default(false)->comment('Certificazione ISO 27001');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
};
