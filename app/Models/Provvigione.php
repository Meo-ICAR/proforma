<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
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
        'erogated_at',
        'importo_erogato',
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
        'iscliente',
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
        'upload_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */

    /**
     * Get the pratica that owns the provvigione.
     */
    public function pratica()
    {
        return $this->belongsTo(Pratica::class, 'id_pratica', 'id');
    }

    protected $casts = [
        'importo' => 'decimal:2',
        'importo_effettivo' => 'decimal:2',
        'erogated_at' => 'datetime',
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
        'upload_at' => 'datetime',
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
        return $this->belongsTo(Fornitore::class, 'piva', 'piva');
    }

    public function cliente()
    {
        return $this
            ->belongsTo(Clienti::class, 'istituto_finanziario', 'name')
            ->where('is_active', 1);  // Only include active clients
    }

    public function customer()
    {
        return $this
            ->belongsTo(Client::class, 'denominazione_riferimento', 'name')
            ->where('tipo', 'Cliente');  // Only include clients
    }

    /**
     * Get the proforma associated with the provvigione.
     */
    public function proforma()
    {
        return $this->belongsTo(Proforma::class);
    }

    /**
     * Get the proforma associated with the provvigione.
     */
    public function compenso()
    {
        return $this->belongsTo(Compenso::class);
    }

    /**
     * Get the stato record associated with the provvigione.
     */
    public function statoRecord()
    {
        return $this->belongsTo(ProvvigioniStato::class, 'stato', 'stato');
    }

    /**
     * Get stato record associated with provvigione.
     */
    public function compensoRecord()
    {
        return $this->belongsTo(Compenso::class, 'stato_compenso', 'stato_compenso');
    }

    /**
     * Get the invoice that this provvigione belongs to (polymorphic).
     */
    public function invoiceable()
    {
        return $this->morphTo();
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

    /**
     * Delete old duplicate records, keeping only the record with highest ID
     * for each group of records with same id_pratica, tipo, denominazione_riferimento, and descrizione
     *
     * @return int Number of deleted records
     */
    public static function deleteOldDuplicates()
    {
        $sql = '
            DELETE p1
            FROM provvigioni p1
            INNER JOIN provvigioni p2 ON
                p1.id_pratica = p2.id_pratica AND
                p1.tipo = p2.tipo AND
                p1.denominazione_riferimento = p2.denominazione_riferimento AND
                p1.descrizione = p2.descrizione
            WHERE p1.id < p2.id
            and p1.data_fattura is null

        ';
        return \DB::delete($sql);
    }

    public static function deleteOldDuplicates2()
    {
        $sql = '
            DELETE p1
            FROM provvigioni p1
            INNER JOIN provvigioni p2 ON
                p1.id_pratica = p2.id_pratica AND
                p1.tipo = p2.tipo AND
                p1.denominazione_riferimento = p2.denominazione_riferimento AND
                p1.descrizione = p2.descrizione
            WHERE p1.id > p2.id
            and p1.data_fattura is null

        ';
        return \DB::delete($sql);
    }
}
