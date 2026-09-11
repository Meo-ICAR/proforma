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
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'importo' => 'decimal:2',
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
}
