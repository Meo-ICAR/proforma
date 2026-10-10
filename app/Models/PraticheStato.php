<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Unico\Core\Models\PraticaStato as CorePraticaStato;

class PraticheStato extends CorePraticaStato
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'isrejected' => 'boolean',
        'isworking' => 'boolean',
        'isestingued' => 'boolean',
    ];
}
