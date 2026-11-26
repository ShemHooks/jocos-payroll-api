<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends HrisModel
{
    protected $table = 'positions';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'salary_grade' => 'integer',
            'requires_prc' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'position_id'
        );
    }

    public function plantillaItems(): HasMany
    {
        return $this->hasMany(
            PlantillaItem::class,
            'position_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForSalaryGrade(
        Builder $query,
        int $salaryGrade
    ): Builder {
        return $query->where(
            'salary_grade',
            $salaryGrade
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

        return $query->where(
            function (Builder $query) use ($search) {
                $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('alias', 'like', "%{$search}%");
            }
        );
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}