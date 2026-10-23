<?php

namespace App\Models;

use App\Services\ApiCallLogger;
use Database\Factories\ApiCallFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Unico\Core\Concerns\UsesCoreConnection;

/**
 * Chiamata a un servizio esterno, registrata da {@see ApiCallLogger}.
 *
 * La tabella `api_calls` è del database condiviso (la crea unicofinance): `provider` resta testo,
 * quindi i valori usati qui ('mediafacile', 'business_central') vanno previsti anche in `ApiProvider` di unicofinance.
 */
class ApiCall extends Model
{
    /** @use HasFactory<ApiCallFactory> */
    use HasFactory;

    use UsesCoreConnection;

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request' => 'array',
            'response' => 'array',
            'cost' => 'decimal:4',
        ];
    }
}
