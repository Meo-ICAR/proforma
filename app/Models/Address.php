<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Unico\Core\Models\Address as CoreAddress;
// use Illuminate\Database\Eloquent\SoftDeletes;
// use Wildside\Userstamps\HasUserstamps;

class Address extends CoreAddress
{
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        //   'deleted_at' => 'datetime',
    ];

    /**
     * Get the parent addressable model (company, fornitore, client, etc.).
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the address type that owns the address.
     */
    public function addressType(): BelongsTo
    {
        return $this->belongsTo(AddressType::class);
    }
}
