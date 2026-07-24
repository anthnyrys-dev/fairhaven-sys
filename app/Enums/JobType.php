<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JobType: string implements HasLabel
{
    case BREAKDOWN = 'breakdown';
    case SCHEDULED_MAINTENANCE = 'scheduled_maintenance';
    case INSTALL = 'install';
    case REPAIR = 'repair';

    public function getLabel(): string
    {
        return match ($this) {
            self::BREAKDOWN => 'Breakdown',
            self::SCHEDULED_MAINTENANCE => 'Scheduled Maintenance',
            self::INSTALL => 'Install',
            self::REPAIR => 'Repair',
        };
    }
}
