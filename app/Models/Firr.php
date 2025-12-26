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
    public static function calculateContributo(float $totalAmount, string $enasarco, int $competenza): float
    {
        // Recuperiamo gli scaglioni ordinati per importo minimo
        $brackets = static::query()
            ->where('competenza', $competenza)
            ->where('enasarco', $enasarco)
            ->orderBy('minimo', 'asc')
            ->get();
        $totalContribution = 0;
        $remainingAmount = $totalAmount;
        $previousMax = 0;
        foreach ($brackets as $bracket) {
            if ($remainingAmount <= 0) {
                break;
            }
            // Calculate the bracket range
            $bracketMin = (float) $bracket->minimo;
            $bracketMax = $bracket->massimo ? (float) $bracket->massimo : PHP_FLOAT_MAX;

            // Calculate the amount in this bracket
            $amountInBracket = min($remainingAmount, $bracketMax - $bracketMin);

            if ($amountInBracket > 0) {
                $totalContribution += $amountInBracket * ($bracket->aliquota / 100);
                $remainingAmount -= $amountInBracket;
            }
        }
        return round($totalContribution, 2);
    }
}
