<?php

namespace App\Models\Hris;

use App\Models\EmployeeAppointmentApproval;
use App\Models\HrisSyncRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends HrisModel
{
    protected $table = 'appointments';

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    public const RATE_PERIOD_DAILY = 'daily';
    public const RATE_PERIOD_MONTHLY = 'monthly';

    public const PAY_SCHEDULE_10_25 = '10/25';
    public const PAY_SCHEDULE_15_30 = '15/30';

    protected $hidden = [
        'current_employee_id',
        'source_action_id',
        'legacy_status',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'employee_id' => 'integer',
            'nature_id' => 'integer',
            'type_id' => 'integer',
            'department_id' => 'integer',
            'division_id' => 'integer',
            'assigned_department_id' => 'integer',
            'position_id' => 'integer',
            'plantilla_item_id' => 'integer',
            'department_fund_source_id' => 'integer',

            'salary_grade' => 'integer',
            'step' => 'integer',

            'rate' => 'decimal:2',

            'step_date' => 'date:Y-m-d',
            'effective_date' => 'date:Y-m-d',
            'assumption_date' => 'date:Y-m-d',
            'csc_approval_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',

            'is_current' => 'boolean',

            'source_action_id' => 'integer',
            'current_employee_id' => 'integer',

            'created_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HRIS Relationships
    |--------------------------------------------------------------------------
    */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id'
        );
    }

    public function nature(): BelongsTo
    {
        return $this->belongsTo(
            EmploymentNature::class,
            'nature_id'
        );
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(
            AppointmentType::class,
            'type_id'
        );
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'department_id'
        );
    }

    public function assignedDepartment(): BelongsTo
    {
        return $this->belongsTo(
            Department::class,
            'assigned_department_id'
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

    public function plantillaItem(): BelongsTo
    {
        return $this->belongsTo(
            PlantillaItem::class,
            'plantilla_item_id'
        );
    }

    public function departmentFundSource(): BelongsTo
    {
        return $this->belongsTo(
            DepartmentFundSource::class,
            'department_fund_source_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Local Payroll Relationships
    |--------------------------------------------------------------------------
    */

    public function syncRequests(): HasMany
    {
        return $this->hasMany(
            HrisSyncRequest::class,
            'hris_appointment_id',
            'id'
        );
    }

    public function payrollApprovals(): HasMany
    {
        return $this->hasMany(
            EmployeeAppointmentApproval::class,
            'hris_appointment_id',
            'id'
        );
    }

    public function currentPayrollApprovals(): HasMany
    {
        return $this->payrollApprovals()
            ->where('is_current', true)
            ->where(
                'status',
                EmployeeAppointmentApproval::STATUS_APPROVED
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Appointment Status Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCurrent(
        Builder $query
    ): Builder {
        return $query->where('is_current', true);
    }

    public function scopeHistorical(
        Builder $query
    ): Builder {
        return $query->where('is_current', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Employee and Organizational Scopes
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

    public function scopeForDepartment(
        Builder $query,
        int $departmentId
    ): Builder {
        return $query->where(
            'department_id',
            $departmentId
        );
    }

    public function scopeForAssignedDepartment(
        Builder $query,
        int $departmentId
    ): Builder {
        return $query->where(
            'assigned_department_id',
            $departmentId
        );
    }

    public function scopeForDivision(
        Builder $query,
        int $divisionId
    ): Builder {
        return $query->where(
            'division_id',
            $divisionId
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

    public function scopeForNature(
        Builder $query,
        int $natureId
    ): Builder {
        return $query->where(
            'nature_id',
            $natureId
        );
    }

    public function scopeForNatureCode(
        Builder $query,
        string $code
    ): Builder {
        return $query->whereHas(
            'nature',
            fn(Builder $query) =>
                $query->where('code', $code)
        );
    }

    public function scopeForAppointmentType(
        Builder $query,
        int $typeId
    ): Builder {
        return $query->where(
            'type_id',
            $typeId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Salary and Pay Schedule Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDailyRated(
        Builder $query
    ): Builder {
        return $query->where(
            'rate_period',
            self::RATE_PERIOD_DAILY
        );
    }

    public function scopeMonthlyRated(
        Builder $query
    ): Builder {
        return $query->where(
            'rate_period',
            self::RATE_PERIOD_MONTHLY
        );
    }

    public function scopeMissingRatePeriod(
        Builder $query
    ): Builder {
        return $query->whereNull('rate_period');
    }

    public function scopeMissingPaySchedule(
        Builder $query
    ): Builder {
        return $query->whereNull('pay_schedule');
    }

    public function scopeForPaySchedule(
        Builder $query,
        string $schedule
    ): Builder {
        return $query->where(
            'pay_schedule',
            $schedule
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Appointment Date Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeEffectiveOn(
        Builder $query,
        string $date
    ): Builder {
        return $query
            ->where('effective_date', '<=', $date)
            ->where(function (Builder $query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }

    public function scopeOverlappingPeriod(
        Builder $query,
        string $periodStart,
        string $periodEnd
    ): Builder {
        return $query
            ->where('effective_date', '<=', $periodEnd)
            ->where(function (Builder $query) use ($periodStart) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $periodStart);
            });
    }

    public function scopeExpired(
        Builder $query,
        ?string $date = null
    ): Builder {
        $date ??= now()->toDateString();

        return $query
            ->whereNotNull('end_date')
            ->where('end_date', '<', $date);
    }

    public function scopeExpiringBetween(
        Builder $query,
        string $startDate,
        string $endDate
    ): Builder {
        return $query->whereBetween(
            'end_date',
            [$startDate, $endDate]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationship Loading
    |--------------------------------------------------------------------------
    */

    public function scopeWithPayrollReferences(
        Builder $query
    ): Builder {
        return $query->with([
            'employee.status',
            'nature',
            'type',
            'department',
            'assignedDepartment',
            'division',
            'position',
            'plantillaItem',
            'departmentFundSource.fundSource',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Appointment Helpers
    |--------------------------------------------------------------------------
    */

    public function isCurrent(): bool
    {
        return (bool) $this->is_current;
    }

    public function isHistorical(): bool
    {
        return !$this->isCurrent();
    }

    public function isDailyRated(): bool
    {
        return $this->rate_period
            === self::RATE_PERIOD_DAILY;
    }

    public function isMonthlyRated(): bool
    {
        return $this->rate_period
            === self::RATE_PERIOD_MONTHLY;
    }

    public function hasValidRatePeriod(): bool
    {
        return in_array(
            $this->rate_period,
            [
                self::RATE_PERIOD_DAILY,
                self::RATE_PERIOD_MONTHLY,
            ],
            true
        );
    }

    public function hasValidPaySchedule(): bool
    {
        return in_array(
            $this->pay_schedule,
            [
                self::PAY_SCHEDULE_10_25,
                self::PAY_SCHEDULE_15_30,
            ],
            true
        );
    }

    public function hasPositiveRate(): bool
    {
        return $this->rate !== null
            && is_numeric($this->rate)
            && (float) $this->rate > 0;
    }

    public function hasEndDate(): bool
    {
        return $this->end_date !== null;
    }

    public function isExpired(
        ?string $date = null
    ): bool {
        $date ??= now()->toDateString();

        return $this->end_date !== null
            && $this->end_date->toDateString() < $date;
    }

    public function isEffectiveOn(string $date): bool
    {
        if ($this->effective_date === null) {
            return false;
        }

        if ($this->effective_date->toDateString() > $date) {
            return false;
        }

        return $this->end_date === null
            || $this->end_date->toDateString() >= $date;
    }

    public function hasAssignedDepartment(): bool
    {
        return $this->assigned_department_id !== null;
    }

    public function isAssignedToDifferentDepartment(): bool
    {
        return $this->hasAssignedDepartment()
            && $this->assigned_department_id
            !== $this->department_id;
    }

    public function hasPlantillaItem(): bool
    {
        return $this->plantilla_item_id !== null;
    }

    public function hasFundingReference(): bool
    {
        return $this->department_fund_source_id !== null;
    }

    public function hasCurrentPayrollApproval(): bool
    {
        return $this->currentPayrollApprovals()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Basic Payroll Data Validation
    |--------------------------------------------------------------------------
    */

    public function hasValidAppointmentDates(): bool
    {
        if ($this->effective_date === null) {
            return false;
        }

        return $this->end_date === null
            || $this->end_date->greaterThanOrEqualTo(
                $this->effective_date
            );
    }

    public function getPayrollDataIssues(): array
    {
        $issues = [];

        if (!$this->hasValidRatePeriod()) {
            $issues[] = 'INVALID_RATE_PERIOD';
        }

        if (!$this->hasValidPaySchedule()) {
            $issues[] = 'INVALID_PAY_SCHEDULE';
        }

        if (!$this->hasPositiveRate()) {
            $issues[] = 'INVALID_SALARY_RATE';
        }

        if (!$this->hasValidAppointmentDates()) {
            $issues[] = 'INVALID_APPOINTMENT_DATES';
        }

        return $issues;
    }

    public function hasCompleteBasicPayrollData(): bool
    {
        return $this->getPayrollDataIssues() === [];
    }

    /*
    |--------------------------------------------------------------------------
    | Basic Appointment Snapshot
    |--------------------------------------------------------------------------
    */

    public function toPayrollSnapshot(): array
    {
        return [
            'hris_appointment_id' => (int) $this->id,
            'hris_employee_id' => (int) $this->employee_id,

            'nature_id' => $this->nature_id,
            'type_id' => $this->type_id,

            'department_id' => $this->department_id,
            'division_id' => $this->division_id,

            'assigned_department_id' =>
                $this->assigned_department_id,

            'assigned_office_text' =>
                $this->assigned_office_text,

            'position_id' => $this->position_id,

            'plantilla_item_id' =>
                $this->plantilla_item_id,

            'department_fund_source_id' =>
                $this->department_fund_source_id,

            'salary_grade' => $this->salary_grade,
            'step' => $this->step,

            'step_date' =>
                $this->step_date?->format('Y-m-d'),

            'salary_rate' => $this->rate,
            'salary_period' => $this->rate_period,
            'pay_schedule' => $this->pay_schedule,

            'effective_date' =>
                $this->effective_date?->format('Y-m-d'),

            'assumption_date' =>
                $this->assumption_date?->format('Y-m-d'),

            'csc_approval_date' =>
                $this->csc_approval_date?->format('Y-m-d'),

            'end_date' =>
                $this->end_date?->format('Y-m-d'),

            'nature_of_work' => $this->nature_of_work,

            'nature_of_work_specify' =>
                $this->nature_of_work_specify,

            'is_current' => (bool) $this->is_current,
        ];
    }
}