<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TechnicianRole: string implements HasLabel
{
    case TECHNICIAN = 'technician';
    case SENIOR_TECHNICIAN = 'senior_technician';

    public function getLabel(): string
    {
        return match ($this) {
            self::TECHNICIAN => 'Technician',
            self::SENIOR_TECHNICIAN => 'Senior Technician',
        };
    }
}
