<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmploymentNature extends HrisModel
{
    protected $table = 'employment_natures';

    public const JO = 'JO';
    public const COS = 'COS';
    public const CASUAL = 'CASUAL';
    public const REGULAR = 'REGULAR';
    public const COTERMINOUS = 'COTERMINOUS';
    public const ELECTED = 'ELECTED';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'renewal_months' => 'integer',
            'allows_salary_increase' => 'boolean',
            'allows_step_increment' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'nature_id'
        );
    }

    public function departmentFundSources(): HasMany
    {
        return $this->hasMany(
            DepartmentFundSource::class,
            'nature_id'
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

    public function isJobOrder(): bool
    {
        return $this->code === self::JO;
    }

    public function isContractOfService(): bool
    {
        return $this->code === self::COS;
    }

    public function isCasual(): bool
    {
        return $this->code === self::CASUAL;
    }

    public function isRegular(): bool
    {
        return $this->code === self::REGULAR;
    }

    public function isCoterminous(): bool
    {
        return $this->code === self::COTERMINOUS;
    }

    public function isElected(): bool
    {
        return $this->code === self::ELECTED;
    }
}