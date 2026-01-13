<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenasarcoTrimestre extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'venasarcotrimestre';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'competenza',
        'Trimestre',
        'produttore',
        'enasarco',
        'montante',
        'contributo'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'competenza' => 'integer',
        'Trimestre' => 'integer',
        'montante' => 'decimal:2',
        'contributo' => 'decimal:8',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'enasarco' => 'plurimandatario',
    ];
}
