<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compenso extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'compensos';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'status_compenso';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

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
        'status_compenso',
        'isperfezionato'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'isperfezionato' => 'boolean',
    ];

      /**
     * Get the stato record associated with the provvigione.
     */
    public function provvigione()
    {
        return $this->HasMany(Provvigione::class, 'stato_compenso', 'status_compenso');
    }

}
