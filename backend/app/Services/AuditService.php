<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\User;

class AuditService
{
    public function log(Ticket $ticket, ActivityAction $action, ?User $actor, array $metadata = []): ActivityLog
    {
        return ActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => $actor?->id,
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }
}
