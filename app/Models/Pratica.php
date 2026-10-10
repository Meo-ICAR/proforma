<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Unico\Core\Models\Pratica as CorePratica;

class Pratica extends CorePratica
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data_inserimento_pratica' => 'date',
        'rata' => 'decimal:2',
        'erogato' => 'decimal:2',
        'nrate' => 'integer',
        'sended_at' => 'date',
        'rejected_at' => 'date',
        'approved_at' => 'date',
        'erogated_at' => 'date',
        'amount' => 'decimal:2',
        'net' => 'decimal:2',
        'is_notowned' => 'boolean',
        'upload_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the agent (fornitore) associated with the pratica.
     */
    public function agente()
    {
        return $this->belongsTo(Fornitore::class, 'partita_iva_agente', 'piva');
    }

    /**
     * Get the istituto (cliente) associated with the pratica, matched by name.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clienti::class, 'denominazione_banca', 'name');
    }

    /**
     * Stato della pratica (tabella `pratica_stati` del pacchetto, tramite `pratica_stato_id`).
     */
    public function stato(): BelongsTo
    {
        return $this->belongsTo(PraticheStato::class, 'pratica_stato_id');
    }

    /**
     * Compatibilità con i vecchi campi di testo `pratiches.stato_pratica` e `pratiches.tipo_prodotto`: import e form
     * continuano a leggerli e scriverli come testo, il modello li traduce in `pratica_stato_id` e `tipoprodotto_id`
     * (uno stato o un tipo nuovo viene creato). Si gestiscono qui e non con gli accessor, perché `tipo_prodotto` darebbe un
     * metodo omonimo (in PHP senza distinzione di maiuscole) della relazione `tipoprodotto()` del pacchetto.
     */
    /**
     * Le chiavi che non sono colonne reali vengono scartate dall'assegnazione di massa: i due attributi di testo devono
     * restare assegnabili (import, form).
     */
    protected function isGuardableColumn($key): bool
    {
        return in_array($key, ['stato_pratica', 'tipo_prodotto'], true) ? true : parent::isGuardableColumn($key);
    }

    public function getAttribute($key)
    {
        return match ($key) {
            'stato_pratica' => $this->stato?->codice,
            'tipo_prodotto' => $this->tipoprodotto?->tipo_prodotto,
            default => parent::getAttribute($key),
        };
    }

    public function setAttribute($key, $value)
    {
        if ($key === 'stato_pratica') {
            $this->attributes['pratica_stato_id'] = blank($value) ? null : PraticheStato::firstOrCreate(['codice' => $value], ['name' => $value])->getKey();
            $this->unsetRelation('stato');

            return $this;
        }

        if ($key === 'tipo_prodotto') {
            $this->attributes['tipoprodotto_id'] = blank($value) ? null : TipoProdotto::firstOrCreate(['tipo_prodotto' => $value], ['name' => $value])->getKey();
            $this->unsetRelation('tipoprodotto');

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function annullato()
    {
        return (bool) $this->stato?->is_rejected;
    }

    /**
     * Get the agent (fornitore) associated with the pratica.
     */
    public function provvigioni()
    {
        return $this->hasMany(Provvigione::class, 'pratica_id');
    }

    public function primaNotaEntries(): MorphMany
    {
        return $this->morphMany(PrimaNotaEntry::class, 'record');
    }
}
