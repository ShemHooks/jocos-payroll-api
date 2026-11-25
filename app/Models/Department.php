<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizational_unit_id',
        'office_code',
        'name',
        'abbreviation',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(
            OrganizationalUnit::class
        );
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(
            EmploymentContract::class
        );
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMapped(Builder $query): Builder
    {
        return $query->whereNotNull(
            'organizational_unit_id'
        );
    }

    public function scopeUnmapped(Builder $query): Builder
    {
        return $query->whereNull(
            'organizational_unit_id'
        );
    }
}