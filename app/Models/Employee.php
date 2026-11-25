<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_no',
        'first_name',
        'middle_name',
        'last_name',
        'name_extension',
        'birth_date',
        'sex',
        'email',
        'mobile_number',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(
            EmploymentContract::class
        );
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->name_extension,
        ]));
    }
}