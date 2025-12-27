<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vcoge extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vcoge';

    /**
     * The primary key for the model.
     * Since this is a view, it doesn't have a primary key
     */

    // protected $keyType = 'string';

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
        'mese',
        'entrata',
        'uscita',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'entrata' => 'decimal:2',
        'uscita' => 'decimal:2',
    ];

    /**
     * Get the mese attribute in a formatted way
     */
    public function getMeseFormattatoAttribute()
    {
        if (empty($this->mese)) {
            return null;
        }

        $data = \DateTime::createFromFormat('Y-m', $this->mese);
        return $data ? $data->format('m/Y') : $this->mese;
    }

    /**
     * Get the saldo (entrata - uscita)
     */
    public function getSaldoAttribute()
    {
        return $this->entrata - $this->uscita;
    }

    /**
     * Get the saldo (entrata - uscita)
     */
    public function getAnnoAttribute()
    {
        return year($this->mese);
    }

    public static function getDistinctMonths()
    {
        return static::query()
            ->select('mese')
            ->whereNotNull('mese')
            ->orderBy('mese', 'desc')
            ->pluck('mese', 'mese')
            ->map(function ($date) {
                return $date;
            })
            ->toArray();
    }
}
