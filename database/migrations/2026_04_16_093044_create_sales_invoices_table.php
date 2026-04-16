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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('invoiceable_type')->nullable()->comment('Type of model (Client, Principal, etc.)');
            $table->char('invoiceable_id', 36)->nullable()->comment('ID of related model');
            $table->string('number');
            $table->string('order_number')->nullable();
            $table->string('customer_number')->nullable();
            $table->string('customer_name');
            $table->string('currency_code')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_including_vat', 10, 2);
            $table->decimal('residual_amount', 10, 2);
            $table->string('ship_to_code')->nullable();
            $table->string('ship_to_cap')->nullable();
            $table->date('registration_date');
            $table->string('agent_code')->nullable();
            $table->string('cdc_code')->nullable();
            $table->string('dimensional_link_code')->nullable();
            $table->string('location_code')->nullable();
            $table->integer('printed_copies')->default(0);
            $table->string('payment_condition_code')->nullable();
            $table->tinyInteger('closed')->default(0);
            $table->tinyInteger('cancelled')->default(0);
            $table->tinyInteger('corrected')->default(0);
            $table->tinyInteger('is_nopractice')->default(0);
            $table->tinyInteger('email_sent')->default(0);
            $table->dateTime('email_sent_at')->nullable();
            $table->text('bill_to_address')->nullable();
            $table->text('bill_to_city')->nullable();
            $table->string('bill_to_province')->nullable();
            $table->text('ship_to_address')->nullable();
            $table->text('ship_to_city')->nullable();
            $table->string('payment_method_code')->nullable();
            $table->string('customer_category')->nullable();
            $table->decimal('exchange_rate', 10, 2)->nullable();
            $table->string('vat_number')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('document_type');
            $table->string('credit_note_linked')->nullable();
            $table->tinyInteger('in_order')->default(0);
            $table->string('supplier_number')->nullable();
            $table->text('supplier_description')->nullable();
            $table->string('purchase_invoice_origin')->nullable();
            $table->tinyInteger('sent_to_sdi')->default(0);
            $table->decimal('document_residual_amount', 10, 2)->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'number']);
            $table->index(['company_id', 'customer_number']);
            $table->index(['company_id', 'registration_date']);
            $table->index(['company_id', 'agent_code']);
            $table->index(['company_id', 'document_type']);
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
        Schema::dropIfExists('sales_invoices');
    }
};
