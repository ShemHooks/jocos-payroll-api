<?php

namespace App\Models;

use App\Enums\OrganizationalUnitType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationalUnit extends Model
{
    use HasFactory;

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

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_organizational_units'
        )->withTimestamps();
    }

    public function userAssignments(): HasMany
    {
        return $this->hasMany(
            UserOrganizationalUnit::class
        );
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
}