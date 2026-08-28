<?php

namespace App\Services;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetricService
{
    /**
     * Get operational summary metrics for Dashboard.
     */
    public function getDashboardMetrics(?User $user = null): array
    {
        $baseQuery = Ticket::query();
        if ($user && $user->isRequester()) {
            $baseQuery->where('requester_id', $user->id);
        }

        $total = (clone $baseQuery)->count();
        $open = (clone $baseQuery)->open()->count();
        $unassigned = (clone $baseQuery)->open()->whereNull('agent_id')->count();
        $inProgress = (clone $baseQuery)->where('status', TicketStatus::IN_PROGRESS->value)->count();
        $breached = (clone $baseQuery)->breached()->count();

        // Resolved tickets for SLA compliance and averages
        $resolvedQuery = (clone $baseQuery)->whereNotNull('resolved_at');
        $resolvedCount = (clone $resolvedQuery)->count();
        $resolvedWithinSla = (clone $resolvedQuery)->where('is_sla_breached', false)->count();

        $complianceRate = $resolvedCount > 0 
            ? round(($resolvedWithinSla / $resolvedCount) * 100, 1) 
            : 100.0;

        // Average response time in minutes (for tickets that received a response)
        $respondedTickets = (clone $baseQuery)
            ->whereNotNull('responded_at')
            ->select('created_at', 'responded_at')
            ->get();

        $totalResponseMinutes = 0;
        foreach ($respondedTickets as $ticket) {
            $totalResponseMinutes += Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->responded_at));
        }
        $avgResponseMinutes = $respondedTickets->count() > 0 
            ? round($totalResponseMinutes / $respondedTickets->count()) 
            : 24;

        // Average resolution time in minutes
        $resolvedTickets = (clone $resolvedQuery)
            ->select('created_at', 'resolved_at')
            ->get();

        $totalResolutionMinutes = 0;
        foreach ($resolvedTickets as $ticket) {
            $totalResolutionMinutes += Carbon::parse($ticket->created_at)->diffInMinutes(Carbon::parse($ticket->resolved_at));
        }
        $avgResolutionMinutes = $resolvedTickets->count() > 0 
            ? round($totalResolutionMinutes / $resolvedTickets->count()) 
            : 180;

        // Breakdown by priority
        $byPriority = [];
        foreach (TicketPriority::cases() as $p) {
            $byPriority[$p->value] = (clone $baseQuery)->where('priority', $p->value)->count();
        }

        // Breakdown by status
        $byStatus = [];
        foreach (TicketStatus::cases() as $s) {
            $byStatus[$s->value] = (clone $baseQuery)->where('status', $s->value)->count();
        }

        return [
            'total_tickets' => $total,
            'open_tickets' => $open,
            'unassigned_tickets' => $unassigned,
            'in_progress_tickets' => $inProgress,
            'breached_tickets' => $breached,
            'sla_compliance_percentage' => $complianceRate,
            'avg_response_time_minutes' => $avgResponseMinutes,
            'avg_resolution_time_minutes' => $avgResolutionMinutes,
            'tickets_by_priority' => $byPriority,
            'tickets_by_status' => $byStatus,
        ];
    }

    /**
     * Get workload per support agent.
     */
    public function getAgentWorkload(): array
    {
        $agents = User::where('role', UserRole::AGENT->value)
            ->with('department')
            ->get();

        $workload = [];
        foreach ($agents as $agent) {
            $assignedOpen = Ticket::where('agent_id', $agent->id)->open()->count();
            $inProgress = Ticket::where('agent_id', $agent->id)->where('status', TicketStatus::IN_PROGRESS->value)->count();
            $resolvedToday = Ticket::where('agent_id', $agent->id)
                ->whereNotNull('resolved_at')
                ->whereDate('resolved_at', Carbon::today())
                ->count();
            $breached = Ticket::where('agent_id', $agent->id)->breached()->count();

            // Capacity percentage: 10 active tickets = 100% capacity
            $capacityPercentage = min(100, round(($assignedOpen / 10) * 100));

            $workload[] = [
                'agent_id' => $agent->id,
                'name' => $agent->name,
                'email' => $agent->email,
                'department' => $agent->department?->name ?? 'Desktop Support',
                'active_tickets_count' => $assignedOpen,
                'in_progress_count' => $inProgress,
                'resolved_today_count' => $resolvedToday,
                'breached_count' => $breached,
                'capacity_percentage' => $capacityPercentage,
            ];
        }

        return $workload;
    }

    /**
     * Get trend and distribution reporting data for ECharts.
     */
    public function getReportsTrends(): array
    {
        // 1. Categories breakdown
        $categories = Category::withCount('tickets')->get();
        $categoryBreakdown = $categories->map(fn($cat) => [
            'name' => $cat->name,
            'count' => $cat->tickets_count,
        ]);

        // 2. Ticket Volume over last 7 days
        $volumeTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $createdCount = Ticket::whereDate('created_at', $date)->count();
            $resolvedCount = Ticket::whereDate('resolved_at', $date)->count();

            $volumeTrend[] = [
                'date' => $date->format('M d'),
                'created' => $createdCount,
                'resolved' => $resolvedCount,
            ];
        }

        // 3. SLA Compliance by priority
        $slaByPriority = [];
        foreach (TicketPriority::cases() as $priority) {
            $total = Ticket::where('priority', $priority->value)->whereNotNull('resolved_at')->count();
            $compliant = Ticket::where('priority', $priority->value)
                ->whereNotNull('resolved_at')
                ->where('is_sla_breached', false)
                ->count();

            $rate = $total > 0 ? round(($compliant / $total) * 100, 1) : 100.0;
            $slaByPriority[] = [
                'priority' => ucfirst($priority->value),
                'compliance_rate' => $rate,
                'total_resolved' => $total,
            ];
        }

        return [
            'category_breakdown' => $categoryBreakdown,
            'volume_trend' => $volumeTrend,
            'sla_by_priority' => $slaByPriority,
        ];
    }
}
