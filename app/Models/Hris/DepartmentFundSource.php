<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartmentFundSource extends HrisModel
{
    protected $table = 'department_fund_sources';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'department_id' => 'integer',
            'nature_id' => 'integer',
            'fund_source_id' => 'integer',
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

    public function nature(): BelongsTo
    {
        return $this->belongsTo(
            EmploymentNature::class,
            'nature_id'
        );
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(
            FundSource::class,
            'fund_source_id'
        );
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'department_fund_source_id'
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

    public function scopeForNature(
        Builder $query,
        int $natureId
    ): Builder {
        return $query->where(
            'nature_id',
            $natureId
        );
    }

    public function scopeForFundSource(
        Builder $query,
        int $fundSourceId
    ): Builder {
        return $query->where(
            'fund_source_id',
            $fundSourceId
        );
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}