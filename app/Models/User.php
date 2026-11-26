<?php

namespace App\Models;

use App\Enums\RoleCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mariadb';

    protected $fillable = [
        'name',
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

    /*
    |--------------------------------------------------------------------------
    | Role Relationships
    |--------------------------------------------------------------------------
    */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Organizational Relationships
    |--------------------------------------------------------------------------
    */

    public function organizationalUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            OrganizationalUnit::class,
            'user_organizational_units',
            'user_id',
            'organizational_unit_id'
        )->withTimestamps();
    }

    public function organizationalAssignments(): HasMany
    {
        return $this->hasMany(
            UserOrganizationalUnit::class,
            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HRIS Synchronization Relationships
    |--------------------------------------------------------------------------
    */

    public function reviewedSyncRequests(): HasMany
    {
        return $this->hasMany(
            HrisSyncRequest::class,
            'reviewed_by'
        );
    }

    public function approvedSyncRequests(): HasMany
    {
        return $this->hasMany(
            HrisSyncRequest::class,
            'approved_by'
        );
    }

    public function approvedAppointments(): HasMany
    {
        return $this->hasMany(
            EmployeeAppointmentApproval::class,
            'approved_by'
        );
    }

    public function revokedAppointmentApprovals(): HasMany
    {
        return $this->hasMany(
            EmployeeAppointmentApproval::class,
            'revoked_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    public function hasRole(RoleCode|string $role): bool
    {
        $code = $role instanceof RoleCode
            ? $role->value
            : $role;

        return $this->roles()
            ->where('roles.code', $code)
            ->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        $codes = array_map(
            fn(RoleCode|string $role) =>
                $role instanceof RoleCode
                ? $role->value
                : $role,
            $roles
        );

        if (empty($codes)) {
            return false;
        }

        return $this->roles()
            ->whereIn('roles.code', $codes)
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Helpers
    |--------------------------------------------------------------------------
    */

    public function hasPermission(string|\BackedEnum $permission): bool
    {
        if (!$this->is_active || $this->must_change_password) {
            return false;
        }

        $code = $permission instanceof \BackedEnum
            ? $permission->value
            : $permission;

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
            ->flatMap(
                fn($role) => $role->permissions->pluck('code')
            )
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | System Role Helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleCode::ADMIN);
    }

    public function isAccounting(): bool
    {
        return $this->hasRole(RoleCode::ACCOUNTING);
    }

    public function isTreasurer(): bool
    {
        return $this->hasRole(RoleCode::TREASURER);
    }

    public function isPayrollEncoder(): bool
    {
        return $this->hasRole(RoleCode::PAYROLL_ENCODER);
    }

    public function isHospitalEncoder(): bool
    {
        return $this->hasRole(RoleCode::HOSPITAL_ENCODER);
    }

    public function isEncoder(): bool
    {
        return $this->hasAnyRole([
            RoleCode::PAYROLL_ENCODER,
            RoleCode::HOSPITAL_ENCODER,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Account Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function requiresPasswordChange(): bool
    {
        return $this->must_change_password;
    }

    public function canAccessSystem(): bool
    {
        return $this->isActive()
            && !$this->requiresPasswordChange()
            && $this->roles()->exists();
    }
}