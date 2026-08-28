<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AGENT = 'agent';
    case REQUESTER = 'requester';
    case MANAGER = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::AGENT => 'Support Agent',
            self::REQUESTER => 'Requester',
            self::MANAGER => 'Operations Manager',
        };
    }
}
