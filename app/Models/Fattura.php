<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $stato
 * @property string|null $clienti_id
 * @property float|null $anticipo
 * @property string|null $anticipo_descrizione
 * @property float|null $compenso
 * @property string|null $compenso_descrizione
 * @property float|null $contributo
 * @property string|null $contributo_descrizione
 * @property string|null $annotation
 * @property string|null $emailsubject
 * @property string|null $emailto
 * @property string|null $emailbody
 * @property string|null $emailfrom
 * @property \DateTime|null $sended_at
 * @property \DateTime|null $paid_at
 * @property float|null $delta
 * @property float|null $anticipo_residuo
 * @property string|null $delta_annotation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Clienti|null $cliente
 */
class Fattura extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fatturas';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'stato',
        'clienti_id',
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
        'anticipo_residuo',
        'delta_annotation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'anticipo' => 'decimal:2',
        'compenso' => 'decimal:2',
        'contributo' => 'decimal:2',
        'delta' => 'decimal:2',
        'anticipo_residuo' => 'decimal:2',
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
     * Get the client that owns the invoice.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clienti::class, 'clienti_id');
    }

    /**
     * Scope a query to only include sent invoices.
     */
    public function scopeInviate($query)
    {
        return $query->where('stato', 'Inviato');
    }

    /**
     * Scope a query to only include paid invoices.
     */
    public function scopePagate($query)
    {
        return $query->where('stato', 'Pagato');
    }
}
