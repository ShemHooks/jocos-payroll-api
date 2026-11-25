<?php

namespace App\Enums;

enum ContractStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expired',
            self::CANCELLED => 'Cancelled',
        };
    }
}