<template>
  <div class="space-y-6">
    <!-- View Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-text-primary">
          Operations Command Center
        </h1>
        <p class="text-xs text-text-muted mt-0.5 font-mono">
          Live incident queue velocity &amp; SLA compliance tracking
        </p>
      </div>
      <div class="flex items-center space-x-3">
        <RouterLink
          to="/tickets/new"
          class="inline-flex items-center px-3.5 py-2 rounded bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-sm transition-subtle space-x-1.5"
        >
          <PlusCircle class="w-4 h-4" />
          <span>New Incident Request</span>
        </RouterLink>
      </div>
    </div>

    <!-- Active SLA Alert Banner (when breached tickets exist) -->
    <div
      v-if="slaStore.atRiskTickets.length > 0"
      class="p-4 rounded-lg bg-red-950/40 border border-red-500/40 flex items-center justify-between"
    >
      <div class="flex items-center space-x-3">
        <AlertTriangle class="w-5 h-5 text-red-400 shrink-0" />
        <div>
          <span class="text-sm font-semibold text-red-200">
            {{ slaStore.atRiskTickets.length }} Incident(s) Require Immediate Escalation
          </span>
          <p class="text-xs text-red-300/80 mt-0.5">
            Tickets are approaching or have breached response or resolution SLA targets.
          </p>
        </div>
      </div>
      <RouterLink
        to="/sla"
        class="text-xs font-medium text-red-300 hover:text-white underline underline-offset-4 font-mono shrink-0"
      >
        View Escalation Board &rarr;
      </RouterLink>
    </div>

    <!-- Top Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <StatCard
        title="Open Incidents"
        :value="dashboardStore.metrics?.open_tickets ?? '—'"
        :subtitle="`${dashboardStore.metrics?.unassigned_tickets ?? 0} unassigned`"
      >
        <template #icon>
          <Ticket class="w-4 h-4" />
        </template>
      </StatCard>

      <StatCard
        title="SLA Compliance"
        :value="`${dashboardStore.metrics?.sla_compliance_percentage ?? 100}%`"
        subtitle="Target: ≥ 95.0%"
        :is-positive="(dashboardStore.metrics?.sla_compliance_percentage ?? 100) >= 95"
      >
        <template #icon>
          <ShieldCheck class="w-4 h-4" />
        </template>
      </StatCard>

      <StatCard
        title="Avg Response Time"
        :value="`${dashboardStore.metrics?.avg_response_time_minutes ?? 0}m`"
        subtitle="Target: ≤ 30m"
        :is-positive="true"
      >
        <template #icon>
          <Clock class="w-4 h-4" />
        </template>
      </StatCard>

      <StatCard
        title="Avg Resolution Time"
        :value="`${Math.round((dashboardStore.metrics?.avg_resolution_time_minutes ?? 0) / 60)}h`"
        :subtitle="`${dashboardStore.metrics?.avg_resolution_time_minutes ?? 0} minutes total`"
      >
        <template #icon>
          <CheckCircle2 class="w-4 h-4" />
        </template>
      </StatCard>
    </div>

    <!-- Middle Split: Volume by Priority & Critical Incidents -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left 7 cols: Priority & Status Matrix -->
      <div class="lg:col-span-7 bg-bg-surface border border-border-default rounded-lg p-5">
        <h2 class="text-sm font-semibold text-text-primary uppercase tracking-wider mb-4 flex items-center justify-between">
          <span>Active Queue Distribution</span>
          <span class="text-xs text-text-muted font-mono font-normal">Real-time counts</span>
        </h2>

        <!-- Priority Bars -->
        <div class="space-y-3 mb-6">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider">By Severity Level</div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div
              v-for="p in priorityItems"
              :key="p.key"
              class="p-3 rounded bg-bg-surface-raised border border-border-subtle"
            >
              <div class="flex items-center justify-between">
                <span class="text-xs font-medium" :class="p.colorClass">{{ p.label }}</span>
                <span class="text-base font-bold font-mono tabular-nums text-text-primary">
                  {{ getPriorityCount(p.key) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Status Breakdown -->
        <div class="space-y-3">
          <div class="text-xs font-semibold text-text-secondary uppercase tracking-wider">By Workflow Stage</div>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div
              v-for="s in statusItems"
              :key="s.key"
              class="p-3 rounded bg-bg-surface-raised border border-border-subtle flex items-center justify-between"
            >
              <span class="text-xs text-text-secondary">{{ s.label }}</span>
              <span class="text-sm font-bold font-mono tabular-nums text-text-primary">
                {{ getStatusCount(s.key) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 5 cols: SLA Watchlist -->
      <div class="lg:col-span-5 bg-bg-surface border border-border-default rounded-lg p-5 flex flex-col">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-semibold text-text-primary uppercase tracking-wider">
            SLA Escalation Watchlist
          </h2>
          <RouterLink to="/sla" class="text-xs text-blue-400 hover:text-blue-300 font-mono">
            All ({{ slaStore.atRiskTickets.length }}) &rarr;
          </RouterLink>
        </div>

        <div v-if="slaStore.atRiskTickets.length === 0" class="flex-1 flex flex-col items-center justify-center py-8 text-center">
          <CheckCircle2 class="w-8 h-8 text-emerald-400 mb-2" />
          <span class="text-sm font-medium text-text-primary">All SLA Targets Met</span>
          <span class="text-xs text-text-muted mt-1">No tickets are currently at risk or breached.</span>
        </div>

        <div v-else class="space-y-2.5 flex-1 overflow-y-auto">
          <RouterLink
            v-for="t in slaStore.atRiskTickets.slice(0, 5)"
            :key="t.id"
            :to="`/tickets/${t.id}`"
            class="block p-3 rounded bg-bg-surface-raised border border-border-subtle hover:border-border-strong transition-subtle group"
          >
            <div class="flex items-center justify-between text-xs mb-1">
              <span class="font-mono text-blue-400 font-semibold">{{ t.reference_no }}</span>
              <SlaCountdown
                :resolution-due-at="t.resolution_due_at"
                :resolved-at="t.resolved_at"
                :is-breached="t.is_sla_breached"
                :is-at-risk="t.is_at_risk"
              />
            </div>
            <div class="text-xs text-text-primary font-medium line-clamp-1 group-hover:text-blue-300">
              {{ t.title }}
            </div>
            <div class="mt-2 flex items-center justify-between text-[11px] text-text-muted">
              <span>Req: {{ t.requester?.name }}</span>
              <span>Assignee: {{ t.agent?.name ?? 'Unassigned' }}</span>
            </div>
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Bottom Queue Quick Access Table -->
    <div class="bg-bg-surface border border-border-default rounded-lg overflow-hidden">
      <div class="px-5 py-4 border-b border-border-default flex items-center justify-between">
        <div>
          <h2 class="text-sm font-semibold text-text-primary uppercase tracking-wider">
            Recent Incident Submissions
          </h2>
          <span class="text-xs text-text-muted font-mono">Latest activity across all departments</span>
        </div>
        <RouterLink to="/tickets" class="text-xs text-blue-400 hover:text-blue-300 font-mono">
          Open Full Queue &rarr;
        </RouterLink>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-bg-surface-raised border-b border-border-subtle text-text-muted font-mono uppercase">
            <tr>
              <th class="px-4 py-3">Code</th>
              <th class="px-4 py-3">Incident Title</th>
              <th class="px-4 py-3">Severity</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Requester</th>
              <th class="px-4 py-3">SLA Status</th>
              <th class="px-4 py-3 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle">
            <tr
              v-for="t in ticketStore.tickets.slice(0, 5)"
              :key="t.id"
              class="hover:bg-bg-surface-hover/50 transition-subtle"
            >
              <td class="px-4 py-3 font-mono font-semibold text-blue-400 whitespace-nowrap">
                {{ t.reference_no }}
              </td>
              <td class="px-4 py-3 text-text-primary font-medium max-w-xs truncate">
                {{ t.title }}
              </td>
              <td class="px-4 py-3">
                <PriorityBadge :priority="t.priority" />
              </td>
              <td class="px-4 py-3">
                <StatusBadge :status="t.status" />
              </td>
              <td class="px-4 py-3 text-text-secondary whitespace-nowrap">
                {{ t.requester?.name }}
              </td>
              <td class="px-4 py-3">
                <SlaCountdown
                  :resolution-due-at="t.resolution_due_at"
                  :resolved-at="t.resolved_at"
                  :is-breached="t.is_sla_breached"
                  :is-at-risk="t.is_at_risk"
                />
              </td>
              <td class="px-4 py-3 text-right">
                <RouterLink
                  :to="`/tickets/${t.id}`"
                  class="text-xs font-mono text-blue-400 hover:text-blue-300 underline"
                >
                  Inspect &rarr;
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useSlaStore } from '@/stores/sla'
import { useTicketStore } from '@/stores/ticket'
import StatCard from '@/components/common/StatCard.vue'
import PriorityBadge from '@/components/common/PriorityBadge.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import SlaCountdown from '@/components/common/SlaCountdown.vue'
import type { TicketPriority, TicketStatus } from '@/types'
import {
  Ticket,
  ShieldCheck,
  Clock,
  CheckCircle2,
  AlertTriangle,
  PlusCircle,
} from 'lucide-vue-next'

const dashboardStore = useDashboardStore()
const slaStore = useSlaStore()
const ticketStore = useTicketStore()

const priorityItems: Array<{ key: TicketPriority; label: string; colorClass: string }> = [
  { key: 'critical', label: 'Critical', colorClass: 'text-red-400' },
  { key: 'high', label: 'High', colorClass: 'text-orange-400' },
  { key: 'medium', label: 'Medium', colorClass: 'text-amber-400' },
  { key: 'low', label: 'Low', colorClass: 'text-emerald-400' },
]

const statusItems: Array<{ key: TicketStatus; label: string }> = [
  { key: 'open', label: 'Open' },
  { key: 'assigned', label: 'Assigned' },
  { key: 'in_progress', label: 'In Progress' },
  { key: 'pending', label: 'Pending Customer' },
  { key: 'resolved', label: 'Resolved' },
  { key: 'closed', label: 'Closed' },
]

function getPriorityCount(key: TicketPriority): number {
  return dashboardStore.metrics?.tickets_by_priority?.[key] ?? 0
}

function getStatusCount(key: TicketStatus): number {
  return dashboardStore.metrics?.tickets_by_status?.[key] ?? 0
}

onMounted(async () => {
  await Promise.all([
    dashboardStore.fetchDashboardMetrics(),
    slaStore.fetchAtRiskTickets(60),
    ticketStore.fetchTickets(1),
  ])
})
</script>
