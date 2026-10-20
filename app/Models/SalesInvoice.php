<?php

namespace App\Models;

use App\Models\Clienti;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
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

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Proforma che si possono abbinare a questa fattura (o già abbinate ad essa), secondo le regole di vendita in Proforma::abbinabiliAFatturaVendita. */
    public function proformasAfterRegistration(): HasMany
    {
        // Senza il vincolo sulla colonna vat_number dei proforma: la partita IVA si legge dal fornitore o dal cliente.
        $relation = Relation::noConstraints(fn () => $this->hasMany(Proforma::class, 'vat_number', 'vat_number'));

        $relation->getQuery()->where(fn ($query) => $query
            ->abbinabiliAFatturaVendita($this)
            ->orWhere(fn ($linked) => $linked->where('proformas.invoiceable_type', static::class)->where('proformas.invoiceable_id', $this->getKey())));

        return $relation;
    }

    /** Fatture a cui non è ancora abbinato nessun proforma né una provvigione (tipo Cliente). */
    public function scopeLibere($query)
    {
        return $query
            ->whereNotExists(fn ($proformas) => $proformas->from('proformas')
                ->whereColumn('proformas.invoiceable_id', $this->getTable().'.id')
                ->where('proformas.invoiceable_type', static::class)
                ->whereNull('proformas.deleted_at'))
            ->whereNotExists(fn ($provvigioni) => $provvigioni->from('provvigioni')
                ->whereColumn('provvigioni.invoiceable_id', $this->getTable().'.id')
                ->where('provvigioni.invoiceable_type', static::class)
                ->whereNull('provvigioni.deleted_at'));
    }
}
