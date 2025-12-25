<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venasarcotot extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'venasarcotot';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'segnalatore',
        'montante',
        'minima',
        'massima',
        'X',
        'competenza',
        'enasarco',
        'minimo',
        'massimo',
        'minimale',
        'massimale',
        'aliquota_soc',
        'aliquota_agente',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'montante' => 'decimal:2',
        'minima' => 'decimal:2',
        'massima' => 'decimal:2',
        'competenza' => 'integer',
        'minimo' => 'decimal:2',
        'massimo' => 'decimal:2',
        'minimale' => 'decimal:2',
        'massimale' => 'decimal:2',
        'aliquota_soc' => 'decimal:2',
        'aliquota_agente' => 'decimal:2',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'enasarco' => 'no',
    ];

    /**
     * Get the enasarco options.
     *
     * @return array
     */
    public static function getEnasarcoOptions()
    {
        return [
            'monomandatario' => 'Monomandatario',
            'plurimandatario' => 'Plurimandatario',
            'societa' => 'Società',
            'no' => 'No'
        ];
    }

    /**
     * Scope a query to only include records with a specific enasarco type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfEnasarcoType($query, $type)
    {
        return $query->where('enasarco', $type);
    }

    /**
     * Calculate the contribution amount.
     *
     * @return float
     */
    public function calculateContribution()
    {
        if ($this->montante <= $this->minimo) {
            return $this->minimale;
        }

        if ($this->montante >= $this->massimo) {
            return $this->massimale;
        }

        $contribution = $this->montante * ($this->aliquota_soc + $this->aliquota_agente) / 100;
        return min(max($contribution, $this->minimale), $this->massimale);
    }
}
