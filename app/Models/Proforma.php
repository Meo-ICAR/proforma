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
        return $this->belongsTo(Fornitore::class, 'fornitori_id', 'id');
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

    /**
     * Create a new proforma from a fornitore
     *
     * @param string $fornitori_id
     * @return \App\Models\Proforma
     */
    public static function createFromFornitore(string $fornitori_id): Proforma
    {
        $fornitore = \App\Models\Fornitore::with('company')->findOrFail($fornitori_id);

        $proformaData = [
            'fornitori_id' => $fornitori_id,
            'stato' => 'Inserito',
            'anticipo' => $fornitore->anticipo,
            'anticipo_descrizione' => $fornitore->anticipo_description,
            'compenso' => 0,
            'compenso_descrizione' => $fornitore->company->compenso_descrizione ?? 'Compenso',
            'contributo' => $fornitore->contributo,
            'contributo_descrizione' => $fornitore->contributo_description,
            'emailto' => $fornitore->email,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($fornitore->company) {
            $proformaData['company_id'] = $fornitore->company->id;
            // Use company's email subject if available
            if (!empty($fornitore->company->emailsubject)) {
                $proformaData['emailsubject'] = $fornitore->company->emailsubject;
                $proformaData['emailfrom'] =  $fornitore->company->emailfrom;
            }
        }

        return self::create($proformaData);
    }

    /**
     * Find or create a proforma for a fornitore by P.IVA and return its ID
     *
     * @param string $piva The VAT number to search for
     * @return string The ID of the found or created proforma
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no fornitore is found with the given P.IVA
     */
    public static function findOrCreateByPiva(string $piva): string
    {
        // Find fornitore by P.IVA (case insensitive and ignoring spaces)
        $fornitore = \App\Models\Fornitore::whereRaw("REPLACE(piva, ' ', '') = ?", [str_replace(' ', '', $piva)])
            ->firstOrFail();

        // Check if there's already a proforma in 'Inserito' status for this fornitore
        $existingProforma = self::where('fornitori_id', $fornitore->id)
            ->where('stato', 'Inserito')
            ->first();

        // Return existing proforma ID or create a new one and return its ID
        return $existingProforma ? $existingProforma->id : self::createFromFornitore($fornitore->id)->id;
    }
}
