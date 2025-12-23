<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pratica extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pratiches';

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
        'codice_pratica',
        'nome_cliente',
        'cognome_cliente',
        'codice_fiscale',
        'denominazione_agente',
        'partita_iva_agente',
        'denominazione_banca',
        'tipo_prodotto',
        'denominazione_prodotto',
        'data_inserimento_pratica',
        'stato_pratica',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data_inserimento_pratica' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the agent (fornitore) associated with the pratica.
     */
    public function agente()
    {
        return $this->belongsTo(Fornitori::class, 'partita_iva_agente', 'piva');
    }

    /**
     * Get the status of the pratica.
     */
    public function stato()
    {
        return $this->belongsTo(PraticheStato::class, 'stato_pratica', 'stato_pratica');
    }
}
