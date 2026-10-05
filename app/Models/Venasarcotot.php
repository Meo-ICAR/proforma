<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'produttore', 'montante', 'contributo', 'X', 'imposta', 'firr', 'competenza', 'enasarco'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'montante' => 'decimal:2',
        'contributo' => 'decimal:2',
        'imposta' => 'decimal:2',
        'competenza' => 'integer',
        'firr' => 'decimal:2',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'enasarco' => 'no',
    ];

    // Una riga annuale ha molti trimestri correlati
    public function trimestri(): HasMany
    {
        return $this
            ->hasMany(VenasarcoTrimestre::class, 'produttore', 'produttore')
            ->whereColumn('competenza', 'competenza')
            // Aggiungiamo il limite: solo i trimestri 1, 2 e 3
            ->where('trimestre', '<', 4);
    }

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
}
