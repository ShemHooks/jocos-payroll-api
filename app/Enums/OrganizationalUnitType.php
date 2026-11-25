<?php

namespace App\Enums;

enum OrganizationalUnitType: string
{
    case PROVINCIAL_OFFICE = 'provincial_office';
    case HOSPITAL = 'hospital';

    public function label(): string
    {
        return match ($this) {
            self::PROVINCIAL_OFFICE => 'Provincial Office',
            self::HOSPITAL => 'Hospital',
        };
    }
}