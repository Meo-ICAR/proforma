<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrimaNotaConfig extends Model
{
    protected $fillable = [
        'name',
        'model_type',
        'value_field',
        'is_positive',
        'date_field',
        'conto_dare',
        'conto_dare_description',
        'conto_avere',
        'conto_avere_description',
        'effective_from',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'is_active' => 'boolean',
            'is_positive' => 'boolean',
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
