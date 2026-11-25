<?php

namespace App\Enums;

enum EmploymentType: string
{
    case JO = 'JO';
    case COS = 'COS';

    public function label(): string
    {
        return match ($this) {
            self::JO => 'Job Order',
            self::COS => 'Contract of Service',
        };
    }
}