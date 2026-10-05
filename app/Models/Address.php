<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
// use Wildside\Userstamps\HasUserstamps;

class Address extends Model
{
    // use SoftDeletes;  // , HasUserstamps;

    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'name',
        'numero',
        'street',
        'city',
        'zip_code',
        'address_type_id',
    ];

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
