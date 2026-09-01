<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enasarco extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'minimo',
        'massimo',
        'aliquota',
        'competenza',
        'enasarco',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'minimo' => 'decimal:2',
        'massimo' => 'decimal:2',
        'aliquota' => 'decimal:2',
        'competenza' => 'integer',
        'enasarco' => 'string',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'competenza' => 2026,
        'enasarco' => 'monomandatario',
    ];
}
