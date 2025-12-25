<?php

// app/Models/TipoProdotto.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProdotto extends Model
{
    protected $table = 'tipoprodotto';
    protected $primaryKey = 'tipo_prodotto';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['tipo_prodotto'];
}
