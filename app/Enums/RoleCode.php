<?php

namespace App\Enums;

enum RoleCode: string
{
    case ADMIN = 'ADMIN';
    case ACCOUNTING = 'ACCOUNTING';
    case TREASURER = 'TREASURER';
    case PAYROLL_ENCODER = 'PAYROLL_ENCODER';
    case HOSPITAL_ENCODER = 'HOSPITAL_ENCODER';
    case EMPLOYEE = 'EMPLOYEE';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'System Administrator',
            self::ACCOUNTING => 'Accounting Officer',
            self::TREASURER => 'Treasurer',
            self::PAYROLL_ENCODER => 'Payroll Encoder',
            self::HOSPITAL_ENCODER => 'Hospital Encoder',
            self::EMPLOYEE => 'Employee',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN =>
                'Manages system configuration, accounts, access, and master data.',

            self::ACCOUNTING =>
                'Reviews payroll records and accounting transactions.',

            self::TREASURER =>
                'Handles authorized treasury operations and payroll exports.',

            self::PAYROLL_ENCODER =>
                'Prepares payroll for assigned provincial offices.',

            self::HOSPITAL_ENCODER =>
                'Prepares payroll for an assigned hospital.',

            self::EMPLOYEE =>
                'Accesses personal released payroll records and payslips.',
        };
    }

    public function isEncoder(): bool
    {
        return in_array($this, [
            self::PAYROLL_ENCODER,
            self::HOSPITAL_ENCODER,
        ], true);
    }

    public function requiresEmployee(): bool
    {
        return $this === self::EMPLOYEE;
    }
}