<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'competenza',
        'clienti_id',
        'fornitore_piva',
        'fornitore',
        'cliente_piva',
        'cliente',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'delta',
        'sended_at',
        'sended2_at',
        'tax_amount',
        'importo_iva',
        'importo_totale_fornitore',
        'currency',
        'payment_method',
        'status',
        'paid_at',
        'isreconiled',
        'is_notenasarco',
        'xml_data',
        'coge',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_date' => 'datetime',
        'sended_at' => 'datetime',
        'sended2_at' => 'datetime',
        'paid_at' => 'date',
        'total_amount' => 'decimal:2',
        'delta' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'importo_iva' => 'decimal:2',
        'importo_totale_fornitore' => 'decimal:2',
        'isreconiled' => 'boolean',
        'is_notenasarco' => 'boolean',
        'competenza' => 'string',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'competenza' => '2025',
        'currency' => 'EUR',
        'status' => 'imported',
        'isreconiled' => false,
        'is_notenasarco' => false,
    ];

    /**
     * Get the supplier that owns the invoice.
     */
    public function supplier()
    {
        return $this->belongsTo(Fornitori::class, 'fornitore_piva', 'piva');
    }

    /**
     * Get the client that owns the invoice.
     */
    public function client()
    {
        return $this->belongsTo(Clienti::class, 'clienti_id');
    }
}
