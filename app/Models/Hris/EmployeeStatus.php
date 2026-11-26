<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeStatus extends HrisModel
{
    protected $table = 'employee_statuses';

    public const ACTIVE = 'ACTIVE';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_separated' => 'boolean',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(
            Employee::class,
            'status_id'
        );
    }

    public function scopeSeparated(
        Builder $query
    ): Builder {
        return $query->where('is_separated', true);
    }

    public function scopeNonSeparated(
        Builder $query
    ): Builder {
        return $query->where('is_separated', false);
    }

    public function scopeForCode(
        Builder $query,
        string $code
    ): Builder {
        return $query->where('code', $code);
    }

    public function isActive(): bool
    {
        return $this->code === self::ACTIVE;
    }

    public function isSeparated(): bool
    {
        return (bool) $this->is_separated;
    }
}