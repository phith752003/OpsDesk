<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        protected SlaService $slaService,
        protected AuditService $auditService,
    ) {}

    /**
     * Create a new ticket with automatic reference number, SLA binding, and audit logging.
     */
    public function createTicket(array $data, User $requester): Ticket
    {
        return DB::transaction(function () use ($data, $requester) {
            $year = Carbon::now()->year;
            $count = Ticket::whereYear('created_at', $year)->count() + 1;
            $referenceNo = sprintf('OPS-%d-%04d', $year, $count);

            $priority = $data['priority'] instanceof TicketPriority 
                ? $data['priority'] 
                : TicketPriority::from($data['priority']);

            $deadlines = $this->slaService->calculateDeadlines($priority);

            $ticket = Ticket::create([
                'reference_no' => $referenceNo,
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $priority,
                'status' => TicketStatus::OPEN,
                'category_id' => $data['category_id'],
                'requester_id' => $requester->id,
                'agent_id' => $data['agent_id'] ?? null,
                'sla_policy_id' => $deadlines['policy']?->id,
                'response_due_at' => $deadlines['response_due_at'],
                'resolution_due_at' => $deadlines['resolution_due_at'],
                'is_sla_breached' => false,
            ]);

            $this->auditService->log($ticket, ActivityAction::CREATED, $requester, [
                'priority' => $priority->value,
                'category_id' => $data['category_id'],
            ]);

            return $ticket;
        });
    }

    /**
     * Transition status with timestamp updates and audit trail.
     */
    public function updateStatus(Ticket $ticket, TicketStatus $newStatus, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $newStatus, $actor) {
            $oldStatus = $ticket->status;
            if ($oldStatus === $newStatus) {
                return $ticket;
            }

            $updates = ['status' => $newStatus];
            $now = Carbon::now();

            if ($newStatus === TicketStatus::RESOLVED && !$ticket->resolved_at) {
                $updates['resolved_at'] = $now;
                // Check if resolution was breached
                if ($ticket->resolution_due_at && $now->greaterThan($ticket->resolution_due_at)) {
                    $updates['is_sla_breached'] = true;
                }
            } elseif ($newStatus === TicketStatus::CLOSED && !$ticket->closed_at) {
                $updates['closed_at'] = $now;
                if (!$ticket->resolved_at) {
                    $updates['resolved_at'] = $now;
                }
            } elseif ($newStatus === TicketStatus::IN_PROGRESS && $oldStatus === TicketStatus::OPEN) {
                if (!$ticket->responded_at) {
                    $updates['responded_at'] = $now;
                }
            }

            $ticket->update($updates);

            $action = match ($newStatus) {
                TicketStatus::RESOLVED => ActivityAction::RESOLVED,
                TicketStatus::CLOSED => ActivityAction::CLOSED,
                default => ActivityAction::STATUS_CHANGED,
            };

            $this->auditService->log($ticket, $action, $actor, [
                'from' => $oldStatus->value,
                'to' => $newStatus->value,
            ]);

            return $ticket;
        });
    }

    /**
     * Assign ticket to an agent.
     */
    public function assignTicket(Ticket $ticket, ?User $agent, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $agent, $actor) {
            $oldAgent = $ticket->agent?->name ?? 'Unassigned';
            $newAgentName = $agent?->name ?? 'Unassigned';

            $updates = ['agent_id' => $agent?->id];
            if ($ticket->status === TicketStatus::OPEN && $agent !== null) {
                $updates['status'] = TicketStatus::ASSIGNED;
            }

            $ticket->update($updates);

            $this->auditService->log($ticket, ActivityAction::ASSIGNED, $actor, [
                'from' => $oldAgent,
                'to' => $newAgentName,
                'agent_id' => $agent?->id,
            ]);

            return $ticket;
        });
    }

    /**
     * Update priority and recalculate SLA.
     */
    public function updatePriority(Ticket $ticket, TicketPriority $newPriority, User $actor): Ticket
    {
        return DB::transaction(function () use ($ticket, $newPriority, $actor) {
            $oldPriority = $ticket->priority;
            if ($oldPriority === $newPriority) {
                return $ticket;
            }

            $deadlines = $this->slaService->calculateDeadlines($newPriority, $ticket->created_at);

            $ticket->update([
                'priority' => $newPriority,
                'sla_policy_id' => $deadlines['policy']?->id,
                'response_due_at' => $deadlines['response_due_at'],
                'resolution_due_at' => $deadlines['resolution_due_at'],
            ]);

            $this->auditService->log($ticket, ActivityAction::PRIORITY_CHANGED, $actor, [
                'from' => $oldPriority->value,
                'to' => $newPriority->value,
            ]);

            return $ticket;
        });
    }

    /**
     * Add comment or internal note.
     */
    public function addComment(Ticket $ticket, string $body, bool $isInternal, User $author): Comment
    {
        return DB::transaction(function () use ($ticket, $body, $isInternal, $author) {
            $comment = Comment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $author->id,
                'body' => $body,
                'is_internal' => $isInternal,
            ]);

            // If an agent responds publicly to an open ticket, record responded_at
            if (!$isInternal && $author->isStaff() && !$ticket->responded_at) {
                $ticket->update(['responded_at' => Carbon::now()]);
            }

            $action = $isInternal ? ActivityAction::INTERNAL_NOTE_ADDED : ActivityAction::COMMENT_ADDED;
            $this->auditService->log($ticket, $action, $author, [
                'comment_id' => $comment->id,
                'is_internal' => $isInternal,
            ]);

            return $comment;
        });
    }
}
