<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Unico\Core\Models\ClientType as CoreClientType;
// use Wildside\Userstamps\HasUserstamps;

class ClientType extends CoreClientType
{
    protected $casts = [
        'is_person' => 'boolean',
        'is_company' => 'boolean',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
