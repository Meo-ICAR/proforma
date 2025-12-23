<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provvigione extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'provvigioni';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'data_inserimento_compenso',
        'descrizione',
        'tipo',
        'importo',
        'importo_effettivo',
        'status_compenso',
        'data_pagamento',
        'n_fattura',
        'data_fattura',
        'data_status',
        'denominazione_riferimento',
        'entrata_uscita',
        'id_pratica',
        'segnalatore',
        'istituto_finanziario',
        'piva',
        'cf',
        'annullato',
        'coordinamento',
        'stato',
        'proforma_id',
        'legacy_id',
        'invoice_number',
        'cognome',
        'quota',
        'nome',
        'fonte',
        'tipo_pratica',
        'data_inserimento_pratica',
        'data_stipula',
        'prodotto',
        'macrostatus',
        'status_pratica',
        'status_pagamento',
        'data_status_pratica',
        'montante',
        'importo_erogato',
        'sended_at',
        'received_at',
        'paided_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'importo' => 'decimal:2',
        'importo_effettivo' => 'decimal:2',
        'importo_erogato' => 'decimal:2',
        'montante' => 'decimal:2',
        'annullato' => 'boolean',
        'coordinamento' => 'boolean',
        'data_inserimento_compenso' => 'date',
        'data_pagamento' => 'date',
        'data_fattura' => 'date',
        'data_status' => 'date',
        'data_inserimento_pratica' => 'date',
        'data_stipula' => 'date',
        'data_status_pratica' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'sended_at' => 'datetime',
        'received_at' => 'datetime',
        'paided_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'annullato' => false,
    ];

    /**
     * Get the fornitore that owns the provvigione.
     */
    public function fornitore()
    {
        return $this->belongsTo(Fornitori::class, 'piva', 'piva');
    }

    /**
     * Get the proforma associated with the provvigione.
     */
    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    /**
     * Get the pratica associated with the provvigione.
     */
    public function pratica()
    {
        return $this->belongsTo(Pratica::class, 'id_pratica', 'id');
    }

    /**
     * Get the stato record associated with the provvigione.
     */
    public function statoRecord()
    {
        return $this->belongsTo(ProvvigioniStato::class, 'stato', 'stato');
    }

    /**
     * Scope a query to only include active (not cancelled) provvigioni.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('annullato', false);
    }

    /**
     * Scope a query to only include paid provvigioni.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('paided_at');
    }

    /**
     * Scope a query to only include sent provvigioni.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sended_at');
    }

    /**
     * Scope a query to only include received provvigioni.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReceived($query)
    {
        return $query->whereNotNull('received_at');
    }

    /**
     * Scope a query to only include provvigioni with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('stato', $status);
    }
}
