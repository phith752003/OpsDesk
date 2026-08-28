<?php

namespace App\Services;

use App\Enums\TicketPriority;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SlaService
{
    /**
     * Compute response and resolution deadlines from SLA policy based on priority.
     */
    public function calculateDeadlines(TicketPriority $priority, ?Carbon $startTime = null): array
    {
        $start = $startTime ?: Carbon::now();
        $policy = SlaPolicy::where('priority', $priority->value)->first();

        $responseMinutes = $policy ? $policy->response_time_minutes : $priority->defaultResponseMinutes();
        $resolutionMinutes = $policy ? $policy->resolution_time_minutes : $priority->defaultResolutionMinutes();

        return [
            'policy' => $policy,
            'response_due_at' => $start->copy()->addMinutes($responseMinutes),
            'resolution_due_at' => $start->copy()->addMinutes($resolutionMinutes),
        ];
    }

    /**
     * Evaluates and sets breach status for a ticket.
     */
    public function checkAndUpdateBreachStatus(Ticket $ticket): bool
    {
        if ($ticket->is_sla_breached) {
            return true;
        }

        $now = Carbon::now();
        $isBreached = false;

        // Check response breach
        if (!$ticket->responded_at && $ticket->response_due_at && $now->greaterThan($ticket->response_due_at)) {
            $isBreached = true;
        }

        // Check resolution breach
        if (!$ticket->resolved_at && $ticket->resolution_due_at && $now->greaterThan($ticket->resolution_due_at)) {
            $isBreached = true;
        }

        if ($isBreached && !$ticket->is_sla_breached) {
            $ticket->update(['is_sla_breached' => true]);
        }

        return $isBreached;
    }

    /**
     * Retrieve tickets at risk of breaching SLA within given threshold minutes (or already breached).
     */
    public function getAtRiskAndBreachedTickets(int $thresholdMinutes = 30): Collection
    {
        return Ticket::with(['requester', 'agent', 'category', 'slaPolicy'])
            ->open()
            ->where(function ($query) use ($thresholdMinutes) {
                $query->atRisk($thresholdMinutes)
                      ->orWhere('is_sla_breached', true);
            })
            ->orderBy('is_sla_breached', 'desc')
            ->orderBy('resolution_due_at', 'asc')
            ->get();
    }
}
