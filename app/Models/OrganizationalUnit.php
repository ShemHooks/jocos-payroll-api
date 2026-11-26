<?php

namespace App\Models;

use App\Enums\OrganizationalUnitType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalUnit extends Model
{
    protected $connection = 'mariadb';
    protected $table = 'organizational_units';

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrganizationalUnitType::class,
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | User Relationships
    |--------------------------------------------------------------------------
    */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_organizational_units',
            'organizational_unit_id',
            'user_id'
        )->withTimestamps();
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(
            UserOrganizationalUnit::class,
            'organizational_unit_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HRIS Department Access Relationships
    |--------------------------------------------------------------------------
    */

    public function departmentAccessMappings(): HasMany
    {
        return $this->hasMany(
            DepartmentAccessMapping::class,
            'organizational_unit_id'
        );
    }

    public function activeDepartmentAccessMappings(): HasMany
    {
        return $this->departmentAccessMappings()
            ->where('is_active', true);
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

    public function scopeHospitals(Builder $query): Builder
    {
        return $query->where(
            'type',
            OrganizationalUnitType::HOSPITAL->value
        );
    }

    public function scopeProvincialOffices(
        Builder $query
    ): Builder {
        return $query->where(
            'type',
            OrganizationalUnitType::PROVINCIAL_OFFICE->value
        );
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim($search ?? '');

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Organizational Type Helpers
    |--------------------------------------------------------------------------
    */

    public function isHospital(): bool
    {
        return $this->type === OrganizationalUnitType::HOSPITAL;
    }

    public function isProvincialOffice(): bool
    {
        return $this->type === OrganizationalUnitType::PROVINCIAL_OFFICE;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /*
    |--------------------------------------------------------------------------
    | HRIS Department Mapping Helpers
    |--------------------------------------------------------------------------
    */

    public function getHrisDepartmentIds(): array
    {
        if (!$this->isActive()) {
            return [];
        }

        return $this->activeDepartmentAccessMappings()
            ->pluck('hris_department_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function hasHrisDepartment(
        int $hrisDepartmentId
    ): bool {
        if (!$this->isActive()) {
            return false;
        }

        return $this->activeDepartmentAccessMappings()
            ->where('hris_department_id', $hrisDepartmentId)
            ->exists();
    }
}