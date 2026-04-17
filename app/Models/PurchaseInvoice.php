<?php

namespace App\Models;

use App\Models\Fornitori;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'number',
        'supplier_invoice_number',
        'supplier_number',
        'supplier',
        'currency_code',
        'amount',
        'amount_including_vat',
        'pay_to_cap',
        'pay_to_country_code',
        'registration_date',
        'location_code',
        'printed_copies',
        'document_date',
        'payment_condition_code',
        'due_date',
        'payment_method_code',
        'residual_amount',
        'closed',
        'cancelled',
        'corrected',
        'is_nopractice',
        'pay_to_address',
        'pay_to_city',
        'supplier_category',
        'exchange_rate',
        'vat_number',
        'fiscal_code',
        'document_type',
        'company_id',
        'invoiceable_type',
        'invoiceable_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_including_vat' => 'decimal:2',
        'residual_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'registration_date' => 'date',
        'document_date' => 'date',
        'due_date' => 'date',
        'closed' => 'boolean',
        'cancelled' => 'boolean',
        'corrected' => 'boolean',
        'is_nopractice' => 'boolean',
        'printed_copies' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function proforma()
    {
        return $this->morphto(Proforma::class, 'purchases');
    }

    public function fornitore()
    {
        return $this->belongsTo(Fornitori::class, 'vat_number', 'piva');
    }

    public function proformas()
    {
        return $this->hasMany(Proforma::class, 'vat_number', 'vat_number');
    }

    public function proformasAfterRegistration()
    {
        return $this->proformas()->when($this->registration_date, function ($query, $registrationDate) {
            return $query->where('sended_at', '<=', $registrationDate);
        });
    }
}
