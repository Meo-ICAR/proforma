<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
// use Wildside\Userstamps\HasUserstamps;

class ClientType extends Model
{
    // use HasUserstamps;

    protected $fillable = [
        'name',
        'is_person',
        'is_company',
        'privacy_role',
        'purpose',
        'data_subjects',
        'data_categories',
        'retention_period',
        'extra_eu_transfer',
        'security_measures',
        'privacy_data',
    ];

    protected $casts = [
        'is_person' => 'boolean',
        'is_company' => 'boolean',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
