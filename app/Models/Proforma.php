<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proforma extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'proformas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stato',
        'fornitori_id',
        'anticipo',
        'anticipo_descrizione',
        'compenso',
        'compenso_descrizione',
        'contributo',
        'contributo_descrizione',
        'annotation',
        'emailsubject',
        'emailto',
        'emailbody',
        'emailfrom',
        'sended_at',
        'paid_at',
        'delta',
        'delta_annotation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'anticipo' => 'decimal:2',
        'compenso' => 'decimal:2',
        'contributo' => 'decimal:2',
        'delta' => 'decimal:2',
        'sended_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'stato' => 'Inserito',
    ];

    /**
     * Get the fornitore that owns the proforma.
     */
    public function fornitore()
    {
        return $this->belongsTo(Fornitori::class, 'fornitori_id', 'id');
    }

    /**
     * Scope a query to only include sent proformas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sended_at');
    }

    /**
     * Scope a query to only include paid proformas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    /**
     * Scope a query to only include proformas with a specific status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('stato', $status);
    }
}
