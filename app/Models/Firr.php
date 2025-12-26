<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firr extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'firrs';

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
        'enasarco'
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
        'enasarco' => 'string'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'competenza' => 2025,
        'enasarco' => 'plurimandatario'
    ];

    /**
     * Calcola il contributo totale basato sugli scaglioni definiti nel DB.
     */
    public static function contributo(float $totalAmount, string $enasarco, int $competenza): float
    {
        // Recuperiamo gli scaglioni ordinati per importo minimo
        $brackets = DB::table('firrs')
            ->where('competenza', $competenza)
            ->where('enasarco', $enasarco)
            ->orderBy('minimo', 'asc')
            ->get();
        $totalContribution = 0;
        if (!empty($brackets)) {
            $remainingAmount = $totalAmount;
            $minimo = 0;
            foreach ($brackets as $bracket) {
                if ($remainingAmount <= 0)
                    break;
                $massimo = $bracket->massimo;
                // Determiniamo quanto del totale cade in questo scaglione
                $maxInBracket = $massimo
                    ? ($massimo - $minimo)
                    : $remainingAmount;

                $taxableInThisBracket = min($remainingAmount, $maxInBracket);

                // Calcolo del contributo per questo scaglione
                $totalContribution += ($taxableInThisBracket * ($bracket->aliquota / 100));

                // Sottraiamo la parte già tassata
                $remainingAmount -= $taxableInThisBracket;
                $minimo = $massimo;
            }
        }
        return round($totalContribution, 2);
    }
}
