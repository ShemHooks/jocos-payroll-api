<?php

namespace App\Models\Hris;

use App\Models\Employee as PayrollEmployee;
use App\Models\HrisSyncRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends HrisModel
{
    protected $table = 'employees';

    protected $hidden = [
        'photo_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'status_id' => 'integer',
            'original_hire_date' => 'date:Y-m-d',
            'separation_date' => 'date:Y-m-d',
            'created_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HRIS Relationships
    |--------------------------------------------------------------------------
    */

    public function status(): BelongsTo
    {
        return $this->belongsTo(
            EmployeeStatus::class,
            'status_id'
        );
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'employee_id'
        );
    }

    public function currentAppointment(): HasOne
    {
        return $this->hasOne(
            Appointment::class,
            'employee_id'
        )->where('is_current', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Payroll Relationships
    |--------------------------------------------------------------------------
    */

    public function payrollEmployee(): HasOne
    {
        return $this->hasOne(
            PayrollEmployee::class,
            'hris_employee_id',
            'id'
        );
    }

    public function syncRequests(): HasMany
    {
        return $this->hasMany(
            HrisSyncRequest::class,
            'hris_employee_id',
            'id'
        );
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
            $this->surname,
            $this->extension,
        ]));
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
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

        return $query->where(
            function (Builder $query) use ($search) {
                $query
                    ->where('employee_no', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhereRaw(
                        "CONCAT_WS(' ', first_name, middle_name, surname, extension) LIKE ?",
                        ["%{$search}%"]
                    );
            }
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas(
            'status',
            fn(Builder $query) =>
                $query->where('code', 'ACTIVE')
        );
    }

    public function scopeSeparated(Builder $query): Builder
    {
        return $query->whereHas(
            'status',
            fn(Builder $query) =>
                $query->where('is_separated', true)
        );
    }

    public function scopeWithCurrentAppointment(
        Builder $query
    ): Builder {
        return $query->whereHas('currentAppointment');
    }

    public function scopeUpdatedSince(
        Builder $query,
        \DateTimeInterface|string $date
    ): Builder {
        return $query->where('updated_at', '>=', $date);
    }

    public function scopeForEmployeeNumber(
        Builder $query,
        string $employeeNumber
    ): Builder {
        return $query->where(
            'employee_no',
            $employeeNumber
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status?->code === 'ACTIVE';
    }

    public function isSeparated(): bool
    {
        return $this->status !== null
            && (bool) $this->status->is_separated;
    }

    public function hasCurrentAppointment(): bool
    {
        return $this->currentAppointment()->exists();
    }

    public function isRegisteredInPayroll(): bool
    {
        return $this->payrollEmployee()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Payroll Employee Snapshot
    |--------------------------------------------------------------------------
    */

    public function toPayrollSnapshot(): array
    {
        return [
            'hris_employee_id' => (int) $this->id,
            'employee_no' => $this->employee_no,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->surname,
            'name_extension' => $this->extension,
            'hris_status_id' => (int) $this->status_id,
            'original_hire_date' =>
                $this->original_hire_date?->format('Y-m-d'),
            'separation_date' =>
                $this->separation_date?->format('Y-m-d'),
        ];
    }
}