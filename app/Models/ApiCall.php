<?php

namespace App\Models;

use App\Services\ApiCallLogger;
use Database\Factories\ApiCallFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Chiamata a un servizio esterno, registrata da {@see ApiCallLogger}. */
class ApiCall extends Model
{
    /** @use HasFactory<ApiCallFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'called_at' => 'datetime',
        ];
    }
}
