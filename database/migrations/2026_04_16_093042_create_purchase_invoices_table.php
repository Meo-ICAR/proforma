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
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->nullable();
            $table->string('supplier_invoice_number')->nullable();
            $table->string('supplier_number')->nullable();
            $table->string('supplier')->nullable();
            $table->string('currency_code')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('amount_including_vat', 10, 2)->nullable();
            $table->string('pay_to_cap')->nullable();
            $table->string('pay_to_country_code')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('location_code')->nullable();
            $table->integer('printed_copies')->default(0);
            $table->date('document_date')->nullable();
            $table->string('payment_condition_code')->nullable();
            $table->date('due_date')->nullable();
            $table->string('payment_method_code')->nullable();
            $table->decimal('residual_amount', 10, 2)->nullable();
            $table->tinyInteger('closed')->default(0);
            $table->tinyInteger('cancelled')->default(0);
            $table->tinyInteger('corrected')->default(0);
            $table->tinyInteger('is_nopractice')->default(0);
            $table->string('pay_to_address')->nullable();
            $table->string('pay_to_city')->nullable();
            $table->string('supplier_category')->nullable();
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->string('vat_number')->nullable();
            $table->string('fiscal_code')->nullable();
            $table->string('document_type')->nullable();
            $table->string('company_id')->nullable();
            $table->string('invoiceable_type')->nullable()->comment('Type of model (Client, Agent, etc.)');
            $table->char('invoiceable_id', 36)->nullable()->comment('ID of related model');
            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'number']);
            $table->index(['company_id', 'supplier']);
            $table->index(['company_id', 'registration_date']);
            $table->index(['invoiceable_type', 'invoiceable_id']);

            // Foreign key
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
