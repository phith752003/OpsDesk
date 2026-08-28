<?php

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::CRITICAL => 'Critical',
        };
    }

    public function defaultResponseMinutes(): int
    {
        return match ($this) {
            self::CRITICAL => 15,
            self::HIGH => 60,
            self::MEDIUM => 240,
            self::LOW => 480,
        };
    }

    public function defaultResolutionMinutes(): int
    {
        return match ($this) {
            self::CRITICAL => 120,
            self::HIGH => 480,
            self::MEDIUM => 1440,
            self::LOW => 2880,
        };
    }
}
