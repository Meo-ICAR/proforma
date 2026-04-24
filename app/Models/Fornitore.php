<?php
// app/Models/Fornitore.php
namespace App\Models;

use App\Models\Proforma;
use App\Models\Provvigione;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Fornitore extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fornitoris';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });

        static::updating(function ($model) {
            // Check if piva is being changed
            if ($model->isDirty('piva')) {
                $oldPiva = $model->getOriginal('piva');
                $newPiva = $model->piva;

                // Update all related provvigioni records with the new P.IVA
                Provvigione::where('piva', $oldPiva)
                    ->update(['piva' => $newPiva]);

                \Log::info("Updated provvigioni P.IVA from {$oldPiva} to {$newPiva} for fornitore: {$model->id}");
            }

            // Check if email is being changed
            if ($model->isDirty('email')) {
                $oldEmail = $model->getOriginal('email');
                $newEmail = $model->email;

                // Update all related proforma records with the new email, but only for 'Inserito' status
                Proforma::where('fornitori_id', $model->id)
                    ->where('stato', 'Inserito')
                    ->update(['emailto' => $newEmail]);

                \Log::info("Updated proforma email from {$oldEmail} to {$newEmail} for fornitore: {$model->id} (only 'Inserito' status)");
            }
        });
    }

    protected $fillable = [
        'codice',
        'coge',
        'name',
        'nome',
        'natoil',
        'indirizzo',
        'comune',
        'cap',
        'prov',
        'tel',
        'coordinatore',
        'piva',
        'cf',
        'nomecoge',
        'nomefattura',
        'email',
        'anticipo',
        'enasarco',
        'anticipo_residuo',
        'contributo',
        'contributo_description',
        'anticipo_description',
        'issubfornitore',
        'operatore',
        'iscollaboratore',
        'isdipendente',
        'regione',
        'citta',
        'company_id',
        'contributoperiodicita',
        'contributodalmese'
    ];

    protected $casts = [
        'natoil' => 'date',
        'anticipo' => 'decimal:2',
        'anticipo_residuo' => 'decimal:2',
        'contributo' => 'decimal:2',
        'issubfornitore' => 'boolean',
        'iscollaboratore' => 'boolean',
        'isdipendente' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'contributo_description' => 'Contributo spese',
        'anticipo_description' => 'Anticipo attuale',
        'issubfornitore' => false,
    ];

    /**
     * Get the company that owns the fornitore.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function proforma()
    {
        return $this->hasMany(Proforma::class, 'fornitori_id');
    }

    public function provvigioni()
    {
        return $this->hasMany(Provvigione::class, 'piva', 'vat_number');
    }

    /**
     * Get all addresses for the fornitore.
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }
}
