<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrimaNotaConfig extends Model
{
    protected $fillable = [
        'event_label',
        'model_type',
        'value_field',
        'date_field',
        'conto_dare',
        'conto_avere',
        'effective_from',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Registrazioni di prima nota generate da questa specifica regola.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(PrimaNotaEntry::class, 'prima_nota_config_id');
    }
}
