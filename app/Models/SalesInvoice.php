<?php

namespace App\Models;

use App\Models\Clienti;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'invoiceable_type',
        'invoiceable_id',
        'number',
        'order_number',
        'customer_number',
        'customer_name',
        'currency_code',
        'due_date',
        'amount',
        'amount_including_vat',
        'residual_amount',
        'ship_to_code',
        'ship_to_cap',
        'registration_date',
        'agent_code',
        'cdc_code',
        'dimensional_link_code',
        'location_code',
        'printed_copies',
        'payment_condition_code',
        'closed',
        'cancelled',
        'corrected',
        'is_nopractice',
        'email_sent',
        'email_sent_at',
        'bill_to_address',
        'bill_to_city',
        'bill_to_province',
        'ship_to_address',
        'ship_to_city',
        'payment_method_code',
        'customer_category',
        'exchange_rate',
        'vat_number',
        'bank_account',
        'document_type',
        'credit_note_linked',
        'in_order',
        'supplier_number',
        'supplier_description',
        'purchase_invoice_origin',
        'sent_to_sdi',
        'document_residual_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_including_vat' => 'decimal:2',
        'residual_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:2',
        'registration_date' => 'date',
        'due_date' => 'date',
        'closed' => 'boolean',
        'cancelled' => 'boolean',
        'corrected' => 'boolean',
        'is_nopractice' => 'boolean',
        'email_sent' => 'boolean',
        'printed_copies' => 'integer',
        'in_order' => 'boolean',
        'sent_to_sdi' => 'boolean',
        'document_residual_amount' => 'decimal:2',
        'email_sent_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function proforma()
    {
        return $this->morphto(Proforma::class, 'sales');
    }

    public function cliente()
    {
        return $this->belongsTo(Clienti::class, 'vat_number', 'piva');
    }

    public function proformas()
    {
        return $this->hasMany(Proforma::class, 'vat_number', 'vat_number');
    }

    public function proformasAfterRegistration()
    {
        return $this->proformas()->when($this->registration_date, function ($query, $registrationDate) {
            return $query->where('sended_at', '>=', $registrationDate);
        });
    }
}
