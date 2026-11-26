<?php

namespace App\Enums;

enum RoleCode: string
{
    case ADMIN = 'admin';
    case ACCOUNTING = 'accounting';
    case TREASURER = 'treasurer';
    case PAYROLL_ENCODER = 'payroll_encoder';
    case HOSPITAL_ENCODER = 'hospital_encoder';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::ACCOUNTING => 'Accounting',
            self::TREASURER => 'Treasurer',
            self::PAYROLL_ENCODER => 'Payroll Encoder',
            self::HOSPITAL_ENCODER => 'Hospital Encoder',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN =>
                'Manages system configuration, user accounts, roles, permissions, and organizational access.',

            self::ACCOUNTING =>
                'Reviews HRIS employee and appointment records, approves synchronization, and reviews payroll.',

            self::TREASURER =>
                'Accesses authorized payroll records and generates treasury exports.',

            self::PAYROLL_ENCODER =>
                'Prepares payroll for assigned provincial offices.',

            self::HOSPITAL_ENCODER =>
                'Prepares payroll for assigned hospitals.',
        };
    }

    public function isEncoder(): bool
    {
        return in_array($this, [
            self::PAYROLL_ENCODER,
            self::HOSPITAL_ENCODER,
        ], true);
    }
}