<?php

namespace App\Models\Hris;

use App\Models\DepartmentAccessMapping;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends HrisModel
{
    protected $table = 'departments';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'department_id'
        );
    }

    public function assignedAppointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'assigned_department_id'
        );
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(
            Division::class,
            'department_id'
        );
    }

    public function plantillaItems(): HasMany
    {
        return $this->hasMany(
            PlantillaItem::class,
            'department_id'
        );
    }

    public function departmentFundSources(): HasMany
    {
        return $this->hasMany(
            DepartmentFundSource::class,
            'department_id'
        );
    }

    public function accessMapping(): HasOne
    {
        return $this->hasOne(
            DepartmentAccessMapping::class,
            'hris_department_id',
            'id'
        );
    }

    public function activeAccessMapping(): HasOne
    {
        return $this->accessMapping()
            ->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim($search ?? '');

        if ($search === '') {
            return $query;
        }

        return $query->where(
            function (Builder $query) use ($search) {
                $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('alias', 'like', "%{$search}%");
            }
        );
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}