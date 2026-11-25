<?php

namespace App\Enums;

enum SalaryPeriod: string
{
    case DAILY = 'daily';
    case MONTHLY = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::DAILY => 'Daily',
            self::MONTHLY => 'Monthly',
        };
    }
}