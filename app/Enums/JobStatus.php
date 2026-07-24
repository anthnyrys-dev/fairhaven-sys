<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JobStatus: string implements HasColor, HasLabel
{
    case OPEN = 'open';
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETE = 'complete';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::SCHEDULED => 'Scheduled',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETE => 'Complete',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OPEN => 'gray',
            self::SCHEDULED => 'info',
            self::IN_PROGRESS => 'warning',
            self::COMPLETE => 'success',
        };
    }

    public function isOutstanding(): bool
    {
        return $this !== self::COMPLETE;
    }
}
