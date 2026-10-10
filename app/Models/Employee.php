<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Unico\Core\Models\Employee as CoreEmployee;

/**
 * Puntatore cross-DB all'anagrafica dipendenti/ruoli, la cui fonte unica è
 * il database di unicooam (vedi App\Models\Employee in unicooam e unicobpm).
 * Usato dal motore RBAC condiviso (vedi App\Models\EmployeeType,
 * App\Models\EmployeeTypePermission e app/helpers.php).
 */
class Employee extends CoreEmployee
{
    use HasFactory, SoftDeletes;

    protected $orderBy = 'name';

    protected $orderDirection = 'asc';

    protected $casts = [
        'is_structure' => 'boolean',
        'is_ghost' => 'boolean',
        'is_external' => 'boolean',
        'oam_at' => 'date',
        'oam_dismissed_at' => 'date',
        'hiring_date' => 'date',
        'termination_date' => 'date',
        'employee_roles' => 'array', // Converte automaticamente JSON <-> Array
    ];

    /**
     * Relazione: Tenant Azienda (sul database di unicooam).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relazione: Account di Login (sul database di unicooam).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione Gerarchica: Il mio Responsabile diretto
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'coordinated_by_id');
    }

    /**
     * Relazione Gerarchica: Le persone che coordino (il mio Team)
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'coordinated_by_id');
    }

    /**
     * Scope generico per filtrare per qualsiasi tipologia di ruolo (JSON).
     */
    public function scopeHasRole(Builder $query, string $role): Builder
    {
        return $query->whereJsonContains('employee_roles', $role);
    }

    public function scopeAuditors(Builder $query): Builder
    {
        return $query->whereJsonContains('employee_roles', 'audit');
    }

    public function scopeQuality(Builder $query): Builder
    {
        return $query->whereJsonContains('employee_roles', 'qualita');
    }

    public function scopeAudits(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereJsonContains('employee_roles', 'qualita')
                ->orWhereJsonContains('employee_roles', 'audit');
        });
    }

    public function scopeEmployee(Builder $query): Builder
    {
        return $query->whereJsonContains('employee_roles', 'dipendente');
    }
}
