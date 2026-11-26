<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Hris\Department as HrisDepartment;

class DepartmentAccessMapping extends Model
{

    protected $connection = 'mariadb';

    protected $table = 'department_access_mappings';


    protected $fillable = [
        'hris_department_id',
        'organizational_unit_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hris_department_id' => 'integer',
            'organizational_unit_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Local Relationships
    |--------------------------------------------------------------------------
    */

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(
            OrganizationalUnit::class,
            'organizational_unit_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HRIS Relationships
    |--------------------------------------------------------------------------
    */

    public function hrisDepartment(): BelongsTo
    {
        return $this->belongsTo(
            HrisDepartment::class,
            'hris_department_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeForOrganizationalUnit(
        Builder $query,
        int $organizationalUnitId
    ): Builder {
        return $query->where(
            'organizational_unit_id',
            $organizationalUnitId
        );
    }

    public function scopeForHrisDepartment(
        Builder $query,
        int $hrisDepartmentId
    ): Builder {
        return $query->where(
            'hris_department_id',
            $hrisDepartmentId
        );
    }

    public function scopeWithActiveOrganizationalUnit(
        Builder $query
    ): Builder {
        return $query->whereHas(
            'organizationalUnit',
            fn(Builder $query) =>
                $query->where('is_active', true)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasActiveOrganizationalUnit(): bool
    {
        return $this->organizationalUnit()
            ->where('is_active', true)
            ->exists();
    }

    public function isMappingEnabled(): bool
    {
        return $this->isActive()
            && $this->hasActiveOrganizationalUnit();
    }
}