<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaItem extends HrisModel
{
    protected $table = 'plantilla_items';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ABOLISHED = 'abolished';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'department_id' => 'integer',
            'division_id' => 'integer',
            'position_id' => 'integer',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'department_id'
        );
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(
            Division::class,
            'division_id'
        );
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(
            Position::class,
            'position_id'
        );
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'plantilla_item_id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_ACTIVE
        );
    }

    public function scopeAbolished(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_ABOLISHED
        );
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

    public function scopeForPosition(
        Builder $query,
        int $positionId
    ): Builder {
        return $query->where(
            'position_id',
            $positionId
        );
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isAbolished(): bool
    {
        return $this->status === self::STATUS_ABOLISHED;
    }
}