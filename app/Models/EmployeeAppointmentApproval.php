<?php

namespace App\Models;

use App\Models\Hris\Appointment as HrisAppointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeAppointmentApproval extends Model
{

    protected $connection = 'mariadb';
    protected $table = 'employee_appointment_approvals';

    /*
    |--------------------------------------------------------------------------
    | Approval Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_APPROVED = 'approved';
    public const STATUS_SUPERSEDED = 'superseded';
    public const STATUS_REVOKED = 'revoked';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment Protection
    |--------------------------------------------------------------------------
    */

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'hris_appointment_id' => 'integer',
            'hris_sync_request_id' => 'integer',

            'version' => 'integer',
            'approved_snapshot' => 'array',

            'approved_by' => 'integer',
            'approved_at' => 'datetime',

            'payroll_effective_date' => 'date:Y-m-d',
            'payroll_end_date' => 'date:Y-m-d',

            'is_current' => 'boolean',

            'supersedes_id' => 'integer',
            'superseded_at' => 'datetime',

            'revoked_by' => 'integer',
            'revoked_at' => 'datetime',
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

    public function syncRequest(): BelongsTo
    {
        return $this->belongsTo(
            HrisSyncRequest::class,
            'hris_sync_request_id'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'revoked_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Appointment Version Relationships
    |--------------------------------------------------------------------------
    */

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'supersedes_id'
        );
    }

    public function succeedingVersions(): HasMany
    {
        return $this->hasMany(
            self::class,
            'supersedes_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Read-Only HRIS Relationship
    |--------------------------------------------------------------------------
    */

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
    | Approval Status Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeApproved(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }

    public function scopeSuperseded(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_SUPERSEDED
        );
    }

    public function scopeRevoked(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_REVOKED
        );
    }

    public function scopeCurrent(
        Builder $query
    ): Builder {
        return $query->where('is_current', true);
    }

    public function scopeCurrentApproved(
        Builder $query
    ): Builder {
        return $query->current()->approved();
    }

    /*
    |--------------------------------------------------------------------------
    | Employee and Appointment Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForEmployee(
        Builder $query,
        int $employeeId
    ): Builder {
        return $query->where(
            'employee_id',
            $employeeId
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

    public function scopeLatestVersion(
        Builder $query
    ): Builder {
        return $query->orderByDesc('version');
    }

    /*
    |--------------------------------------------------------------------------
    | Payroll Date Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeEffectiveOn(
        Builder $query,
        string $date
    ): Builder {
        return $query
            ->where('payroll_effective_date', '<=', $date)
            ->where(function (Builder $query) use ($date) {
                $query->whereNull('payroll_end_date')
                    ->orWhere('payroll_end_date', '>=', $date);
            });
    }

    public function scopeOverlappingPeriod(
        Builder $query,
        string $periodStart,
        string $periodEnd
    ): Builder {
        return $query
            ->where(
                'payroll_effective_date',
                '<=',
                $periodEnd
            )
            ->where(function (Builder $query) use ($periodStart) {
                $query->whereNull('payroll_end_date')
                    ->orWhere(
                        'payroll_end_date',
                        '>=',
                        $periodStart
                    );
            });
    }

    public function scopeHistoricalCandidates(
        Builder $query
    ): Builder {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_SUPERSEDED,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isSuperseded(): bool
    {
        return $this->status === self::STATUS_SUPERSEDED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function isCurrent(): bool
    {
        return $this->is_current;
    }

    public function isCurrentApproved(): bool
    {
        return $this->isCurrent()
            && $this->isApproved();
    }

    public function isFirstVersion(): bool
    {
        return $this->version === 1
            && $this->supersedes_id === null;
    }

    public function hasPreviousVersion(): bool
    {
        return $this->supersedes_id !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Source Integrity Helpers
    |--------------------------------------------------------------------------
    */

    public function matchesSourceHash(
        string $hash
    ): bool {
        return is_string($this->source_hash)
            && hash_equals($this->source_hash, $hash);
    }

    public function snapshotValue(
        string $key,
        mixed $default = null
    ): mixed {
        return data_get(
            $this->approved_snapshot ?? [],
            $key,
            $default
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Display Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_SUPERSEDED => 'Superseded',
            self::STATUS_REVOKED => 'Revoked',
            default => 'Unknown Status',
        };
    }

    public function getVersionLabelAttribute(): string
    {
        return 'Version ' . $this->version;
    }
}