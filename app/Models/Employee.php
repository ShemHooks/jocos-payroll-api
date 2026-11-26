<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Hris\Employee as HrisEmployee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $connection = 'mariadb';
    protected $table = 'employees';

    public const PAYROLL_ACTIVE = 'active';
    public const PAYROLL_ON_HOLD = 'on_hold';
    public const PAYROLL_INACTIVE = 'inactive';

    public const SYNC_SYNCED = 'synced';
    public const SYNC_UPDATE_PENDING = 'update_pending';
    public const SYNC_FAILED = 'sync_failed';

    protected $fillable = [
        'hris_employee_id',
        'employee_no',
        'first_name',
        'middle_name',
        'last_name',
        'name_extension',
        'hris_status_id',
        'original_hire_date',
        'separation_date',
    ];

    protected function casts(): array
    {
        return [
            'hris_employee_id' => 'integer',
            'hris_status_id' => 'integer',
            'original_hire_date' => 'date:Y-m-d',
            'separation_date' => 'date:Y-m-d',
            'hris_updated_at' => 'datetime',
            'hris_synced_at' => 'datetime',
            'hris_last_checked_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function syncRequests(): HasMany
    {
        return $this->hasMany(
            HrisSyncRequest::class,
            'employee_id'
        );
    }

    public function appointmentApprovals(): HasMany
    {
        return $this->hasMany(
            EmployeeAppointmentApproval::class,
            'employee_id'
        );
    }

    public function currentAppointmentApprovals(): HasMany
    {
        return $this->appointmentApprovals()
            ->where('is_current', true)
            ->where('status', 'approved');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->name_extension,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | Payroll Status Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'payroll_status',
            self::PAYROLL_ACTIVE
        );
    }

    public function scopeOnHold(Builder $query): Builder
    {
        return $query->where(
            'payroll_status',
            self::PAYROLL_ON_HOLD
        );
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where(
            'payroll_status',
            self::PAYROLL_INACTIVE
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Synchronization Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSynced(Builder $query): Builder
    {
        return $query->where(
            'sync_status',
            self::SYNC_SYNCED
        );
    }

    public function scopePendingSync(Builder $query): Builder
    {
        return $query->where(
            'sync_status',
            self::SYNC_UPDATE_PENDING
        );
    }

    public function scopeSyncFailed(Builder $query): Builder
    {
        return $query->where(
            'sync_status',
            self::SYNC_FAILED
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Search Scope
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim($search ?? '');

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('employee_no', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('middle_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhereRaw(
                    "CONCAT_WS(' ', first_name, middle_name, last_name, name_extension) LIKE ?",
                    ["%{$search}%"]
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->payroll_status === self::PAYROLL_ACTIVE;
    }

    public function isOnHold(): bool
    {
        return $this->payroll_status === self::PAYROLL_ON_HOLD;
    }

    public function isInactive(): bool
    {
        return $this->payroll_status === self::PAYROLL_INACTIVE;
    }

    public function isSynced(): bool
    {
        return $this->sync_status === self::SYNC_SYNCED;
    }

    public function hasPendingSync(): bool
    {
        return $this->sync_status === self::SYNC_UPDATE_PENDING;
    }

    public function hasSyncFailed(): bool
    {
        return $this->sync_status === self::SYNC_FAILED;
    }

    public function passesBasicPayrollChecks(): bool
    {
        return $this->isActive() && $this->isSynced();
    }

    public function hrisEmployee(): BelongsTo
    {
        return $this->belongsTo(
            HrisEmployee::class,
            'hris_employee_id',
            'id'
        );
    }
}