<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_code',
        'title',
        'salary_grade',
        'standard_monthly_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'salary_grade' => 'integer',
            'standard_monthly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(
            EmploymentContract::class
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}