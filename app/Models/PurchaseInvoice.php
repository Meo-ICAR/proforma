<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

class PurchaseInvoice extends Model
{
    /**
     * Tabella di Proforma: database dell'applicazione (le tabelle condivise di unico-core usano la connessione `core`).
     */
    protected $connection = 'mysql';

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
        return $this->belongsTo(Fornitore::class, 'vat_number', 'piva');
    }

    public function proformas()
    {
        return $this->hasMany(Proforma::class, 'vat_number', 'vat_number');
    }

    /** Proforma che si possono abbinare a questa fattura (o già abbinate ad essa), secondo le regole di acquisto in Proforma::abbinabiliAFatturaAcquisto. */
    public function proformasAfterRegistration(): HasMany
    {
        // Senza il vincolo sulla colonna vat_number dei proforma: la partita IVA si legge dal fornitore o dal cliente.
        $relation = Relation::noConstraints(fn () => $this->hasMany(Proforma::class, 'vat_number', 'vat_number'));

        $relation->getQuery()->where(fn ($query) => $query
            ->abbinabiliAFatturaAcquisto($this)
            ->orWhere(fn ($linked) => $linked->where('proformas.invoiceable_type', static::class)->where('proformas.invoiceable_id', $this->getKey())));

        return $relation;
    }

    /** Fatture a cui non è ancora abbinato nessun proforma. */
    public function scopeLibere($query)
    {
        return $query->whereNotExists(fn ($proformas) => $proformas->from('proformas')
            ->whereColumn('proformas.invoiceable_id', $this->getTable().'.id')
            ->where('proformas.invoiceable_type', static::class)
            ->whereNull('proformas.deleted_at'));
    }
}
