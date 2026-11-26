<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends HrisModel
{
    protected $table = 'divisions';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'department_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'department_id'
        );
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'division_id'
        );
    }

    public function plantillaItems(): HasMany
    {
        return $this->hasMany(
            PlantillaItem::class,
            'division_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDepartment(
        Builder $query,
        int $departmentId
    ): Builder {
        return $query->where(
            'department_id',
            $departmentId
        );
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}