<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'password_reset_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles'
        )->withTimestamps();
    }

    public function organizationalUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganizationalUnit::class,
            'user_organizational_units'
        )->withTimestamps();
    }

    public function organizationalAssignments(): HasMany
    {
        return $this->hasMany(
            UserOrganizationalUnit::class
        );
    }

    // ==========================================
    // ROLE AND PERMISSION HELPERS
    // ==========================================

    public function hasRole(string $code): bool
    {
        return $this->roles()
            ->where('roles.code', $code)
            ->exists();
    }

    public function hasAnyRole(array $codes): bool
    {
        return $this->roles()
            ->whereIn('roles.code', $codes)
            ->exists();
    }

    public function hasPermission(string $code): bool
    {
        if (!$this->is_active || $this->must_change_password) {
            return false;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($code) {
                $query->where('permissions.code', $code);
            })
            ->exists();
    }

    public function getPermissionCodes(): array
    {
        if (!$this->is_active || $this->must_change_password) {
            return [];
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions)
            ->pluck('code')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('ADMIN');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('EMPLOYEE');
    }

    public function isPayrollEncoder(): bool
    {
        return $this->hasRole('PAYROLL_ENCODER');
    }

    public function isHospitalEncoder(): bool
    {
        return $this->hasRole('HOSPITAL_ENCODER');
    }

    public function isEncoder(): bool
    {
        return $this->hasAnyRole([
            'PAYROLL_ENCODER',
            'HOSPITAL_ENCODER',
        ]);
    }
}