<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PrimaNotaEntry extends Model
{
    protected $fillable = [
        'prima_nota_config_id',
        'data',
        'importo',
        'conto_dare',
        'conto_avere',
        'record_type',
        'record_id',
        'synced_at',
        'sync_error',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'importo' => 'decimal:2',
            'synced_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Regola di configurazione di origine.
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(PrimaNotaConfig::class, 'prima_nota_config_id');
    }

    /**
     * Il record sorgente (es. Contratto, Pratica, Pagamento) a cui fa riferimento la nota.
     */
    public function record(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Quando il record collegato è un Proforma, risolve la controparte
     * effettivamente valorizzata su di esso: fornitore (agente, via
     * fornitori_id), cliente (istituto/mandante, tabella Clienti, via lo
     * stesso fornitori_id) o client (cliente/consulente, tabella clients,
     * via client_id). Le tre relazioni sono mutuamente esclusive nella
     * pratica: solo una delle tre risulta valorizzata per un dato proforma.
     * Restituisce null se il record collegato non è un Proforma o se
     * nessuna delle tre controparti risulta impostata.
     */
    public function proformaControparte(): Fornitore|Clienti|Client|null
    {
        if (! $this->record instanceof Proforma) {
            return null;
        }

        return $this->record->fornitore ?? $this->record->cliente ?? $this->record->client;
    }
}
