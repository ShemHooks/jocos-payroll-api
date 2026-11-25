<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Enums\EmploymentType;
use App\Enums\SalaryPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'department_id',
        'position_id',
        'employment_type',
        'salary_rate',
        'salary_period',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'salary_rate' => 'decimal:2',
            'salary_period' => SalaryPeriod::class,
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'status' => ContractStatus::class,
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    public function scopeActive(Builder $query): Builder
    {
        $today = now('Asia/Manila')->toDateString();

        return $query
            ->where('status', ContractStatus::ACTIVE->value)
            ->whereDate('start_date', '<=', $today)
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $today);
            });
    }

    public function scopeExpired(Builder $query): Builder
    {
        $today = now('Asia/Manila')->toDateString();

        return $query
            ->where(function (Builder $query) use ($today) {
                $query->where(
                    'status',
                    ContractStatus::EXPIRED->value
                )->orWhere(function (Builder $query) use ($today) {
                    $query->where(
                        'status',
                        ContractStatus::ACTIVE->value
                    )
                        ->whereNotNull('end_date')
                        ->whereDate('end_date', '<', $today);
                });
            });
    }

    public function scopeExpiringSoon(
        Builder $query,
        int $days = 30
    ): Builder {
        $today = now('Asia/Manila')->startOfDay();
        $until = $today->copy()->addDays(max(0, $days));

        return $query
            ->active()
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [
                $today->toDateString(),
                $until->toDateString(),
            ]);
    }

    // ==========================================
    // STATUS HELPERS
    // ==========================================

    public function isActive(): bool
    {
        $today = now('Asia/Manila')->toDateString();

        return $this->status === ContractStatus::ACTIVE
            && $this->start_date->toDateString() <= $today
            && (
                $this->end_date === null
                || $this->end_date->toDateString() >= $today
            );
    }

    public function isExpired(): bool
    {
        if ($this->status === ContractStatus::CANCELLED) {
            return false;
        }

        if ($this->status === ContractStatus::EXPIRED) {
            return true;
        }

        return $this->end_date !== null
            && $this->end_date->toDateString()
            < now('Asia/Manila')->toDateString();
    }
}