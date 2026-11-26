<?php

namespace App\Models\Hris;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppointmentType extends HrisModel
{
    protected $table = 'appointment_types';

    public const CATEGORY_CSC_APPOINTMENT = 'csc_appointment';
    public const CATEGORY_HRIS_ACTION = 'hris_action';
    public const CATEGORY_SEPARATION = 'separation';

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'type_id'
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

    public function scopeForCategory(
        Builder $query,
        string $category
    ): Builder {
        return $query->where('category', $category);
    }

    public function scopeSeparations(
        Builder $query
    ): Builder {
        return $query->where(
            'category',
            self::CATEGORY_SEPARATION
        );
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isCscAppointment(): bool
    {
        return $this->category
            === self::CATEGORY_CSC_APPOINTMENT;
    }

    public function isHrisAction(): bool
    {
        return $this->category
            === self::CATEGORY_HRIS_ACTION;
    }

    public function isSeparation(): bool
    {
        return $this->category
            === self::CATEGORY_SEPARATION;
    }
}