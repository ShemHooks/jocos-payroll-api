<?php

namespace App\Models;

use App\Models\Hris\Employee as HrisEmployee;
use App\Models\Hris\Appointment as HrisAppointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HrisSyncRequest extends Model
{
    protected $connection = 'mariadb';
    protected $table = 'hris_sync_requests';

    /*
    |--------------------------------------------------------------------------
    | Request Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_NEW_EMPLOYEE = 'new_employee';
    public const TYPE_EMPLOYEE_UPDATE = 'employee_update';
    public const TYPE_NEW_APPOINTMENT = 'new_appointment';
    public const TYPE_CONTRACT_RENEWAL = 'contract_renewal';
    public const TYPE_SALARY_ADJUSTMENT = 'salary_adjustment';
    public const TYPE_PROMOTION = 'promotion';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_APPOINTMENT_UPDATE = 'appointment_update';
    public const TYPE_SEPARATION = 'separation';
    public const TYPE_STATUS_CHANGE = 'status_change';

    /*
    |--------------------------------------------------------------------------
    | Request Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SYNCHRONIZING = 'synchronizing';
    public const STATUS_SYNC_FAILED = 'sync_failed';
    public const STATUS_SYNCED = 'synced';
    public const STATUS_SUPERSEDED = 'superseded';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment Protection
    |--------------------------------------------------------------------------
    */

    protected $guarded = ['*'];

    protected $hidden = [
        'deduplication_key',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'hris_employee_id' => 'integer',
            'hris_appointment_id' => 'integer',
            'employee_id' => 'integer',

            'previous_snapshot' => 'array',
            'source_snapshot' => 'array',

            'reviewed_by' => 'integer',
            'approved_by' => 'integer',

            'detected_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'synced_at' => 'datetime',
            'last_sync_attempt_at' => 'datetime',

            'sync_attempts' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Local Relationships
    |--------------------------------------------------------------------------
    */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function appointmentApproval(): HasOne
    {
        return $this->hasOne(
            EmployeeAppointmentApproval::class,
            'hris_sync_request_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Read-Only HRIS Relationships
    |--------------------------------------------------------------------------
    */

    public function hrisEmployee(): BelongsTo
    {
        return $this->belongsTo(
            HrisEmployee::class,
            'hris_employee_id',
            'id'
        );
    }

    public function hrisAppointment(): BelongsTo
    {
        return $this->belongsTo(
            HrisAppointment::class,
            'hris_appointment_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }

    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_UNDER_REVIEW
        );
    }

    public function scopeReturned(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_RETURNED
        );
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }

    public function scopeSynchronizing(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_SYNCHRONIZING
        );
    }

    public function scopeSynced(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_SYNCED
        );
    }

    public function scopeSyncFailed(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_SYNC_FAILED
        );
    }

    public function scopeSuperseded(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_SUPERSEDED
        );
    }

    public function scopeAwaitingReview(
        Builder $query
    ): Builder {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_UNDER_REVIEW,
        ]);
    }

    public function scopeUnresolved(
        Builder $query
    ): Builder {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_RETURNED,
            self::STATUS_APPROVED,
            self::STATUS_SYNCHRONIZING,
            self::STATUS_SYNC_FAILED,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Request Type Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOfType(
        Builder $query,
        string $type
    ): Builder {
        return $query->where('request_type', $type);
    }

    public function scopeNewEmployees(
        Builder $query
    ): Builder {
        return $query->where(
            'request_type',
            self::TYPE_NEW_EMPLOYEE
        );
    }

    public function scopeAppointmentRelated(
        Builder $query
    ): Builder {
        return $query->whereNotNull(
            'hris_appointment_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Employee and Appointment Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForHrisEmployee(
        Builder $query,
        int $hrisEmployeeId
    ): Builder {
        return $query->where(
            'hris_employee_id',
            $hrisEmployeeId
        );
    }

    public function scopeForHrisAppointment(
        Builder $query,
        int $hrisAppointmentId
    ): Builder {
        return $query->where(
            'hris_appointment_id',
            $hrisAppointmentId
        );
    }

    public function scopeForEmployee(
        Builder $query,
        int $employeeId
    ): Builder {
        return $query->where(
            'employee_id',
            $employeeId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_RETURNED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isSynchronizing(): bool
    {
        return $this->status === self::STATUS_SYNCHRONIZING;
    }

    public function isSynced(): bool
    {
        return $this->status === self::STATUS_SYNCED;
    }

    public function hasSyncFailed(): bool
    {
        return $this->status === self::STATUS_SYNC_FAILED;
    }

    public function isSuperseded(): bool
    {
        return $this->status === self::STATUS_SUPERSEDED;
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow State Helpers
    |--------------------------------------------------------------------------
    */

    public function canBeReviewed(): bool
    {
        return $this->isPending()
            || $this->isUnderReview();
    }

    public function canBeApproved(): bool
    {
        return $this->isUnderReview()
            && $this->reviewed_by !== null
            && $this->reviewed_at !== null;
    }

    public function canBeReturned(): bool
    {
        return $this->isUnderReview();
    }

    public function canBeSynchronized(): bool
    {
        return $this->isApproved()
            && $this->approved_by !== null
            && $this->approved_at !== null;
    }

    public function canBeRetried(): bool
    {
        return $this->hasSyncFailed()
            && $this->approved_by !== null
            && $this->approved_at !== null;
    }

    public function isTerminal(): bool
    {
        return $this->isSynced()
            || $this->isSuperseded();
    }

    /*
    |--------------------------------------------------------------------------
    | Request Helpers
    |--------------------------------------------------------------------------
    */

    public function isNewEmployee(): bool
    {
        return $this->request_type
            === self::TYPE_NEW_EMPLOYEE;
    }

    public function isAppointmentRelated(): bool
    {
        return $this->hris_appointment_id !== null;
    }

    public function hasLocalEmployee(): bool
    {
        return $this->employee_id !== null;
    }

    public function hasPreviousSnapshot(): bool
    {
        return $this->previous_snapshot !== null;
    }

    public function matchesSourceHash(string $hash): bool
    {
        return is_string($this->source_hash)
            && hash_equals($this->source_hash, $hash);
    }

    /*
    |--------------------------------------------------------------------------
    | Display Accessors
    |--------------------------------------------------------------------------
    */

    public function getRequestTypeLabelAttribute(): string
    {
        return match ($this->request_type) {
            self::TYPE_NEW_EMPLOYEE => 'New Employee',
            self::TYPE_EMPLOYEE_UPDATE => 'Employee Update',
            self::TYPE_NEW_APPOINTMENT => 'New Appointment',
            self::TYPE_CONTRACT_RENEWAL => 'Contract Renewal',
            self::TYPE_SALARY_ADJUSTMENT => 'Salary Adjustment',
            self::TYPE_PROMOTION => 'Promotion',
            self::TYPE_TRANSFER => 'Transfer',
            self::TYPE_APPOINTMENT_UPDATE => 'Appointment Update',
            self::TYPE_SEPARATION => 'Separation',
            self::TYPE_STATUS_CHANGE => 'Status Change',
            default => 'Unknown Request',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_SYNCHRONIZING => 'Synchronizing',
            self::STATUS_SYNC_FAILED => 'Synchronization Failed',
            self::STATUS_SYNCED => 'Synchronized',
            self::STATUS_SUPERSEDED => 'Superseded',
            default => 'Unknown Status',
        };
    }
}