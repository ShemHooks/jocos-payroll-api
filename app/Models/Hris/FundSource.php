<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundSource extends HrisModel
{
    protected $table = 'fund_sources';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function departmentFundSources(): HasMany
    {
        return $this->hasMany(
            DepartmentFundSource::class,
            'fund_source_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCode(
        Builder $query,
        string $code
    ): Builder {
        return $query->where('code', $code);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}