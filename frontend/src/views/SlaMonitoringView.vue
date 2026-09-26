<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-text-primary">
          SLA Performance &amp; Escalation Board
        </h1>
        <p class="text-xs text-text-muted mt-0.5 font-mono">
          Incident threshold monitoring to prevent contract and policy breaches
        </p>
      </div>

      <!-- Warning Window Filter -->
      <div class="flex items-center space-x-2 text-xs font-mono">
        <span class="text-text-muted">Threshold Window:</span>
        <select
          v-model="slaStore.thresholdMinutes"
          class="px-2.5 py-1.5 bg-bg-surface border border-border-default rounded text-xs text-text-primary focus:outline-none focus:border-blue-500 font-mono"
          @change="reloadBoard"
        >
          <option :value="15">15 Minutes</option>
          <option :value="30">30 Minutes</option>
          <option :value="60">60 Minutes</option>
          <option :value="120">2 Hours</option>
        </select>
      </div>
    </div>

    <!-- Active Warning Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="p-4 rounded-lg bg-red-950/30 border border-red-500/40">
        <div class="text-[11px] font-mono text-red-300 uppercase tracking-wider">Active SLA Breaches</div>
        <div class="text-2xl font-bold font-mono text-red-400 mt-1">
          {{ breachedCount }}
        </div>
        <div class="text-[11px] text-red-300/70 mt-1">Past guaranteed response or resolution time</div>
      </div>

      <div class="p-4 rounded-lg bg-amber-950/30 border border-amber-500/40">
        <div class="text-[11px] font-mono text-amber-300 uppercase tracking-wider">
          At Risk (&lt;{{ slaStore.thresholdMinutes }}m remaining)
        </div>
        <div class="text-2xl font-bold font-mono text-amber-400 mt-1">
          {{ atRiskCount }}
        </div>
        <div class="text-[11px] text-amber-300/70 mt-1">Approaching breach threshold rapidly</div>
      </div>

      <div class="p-4 rounded-lg bg-bg-surface border border-border-default">
        <div class="text-[11px] font-mono text-text-secondary uppercase tracking-wider">Target Compliance Rate</div>
        <div class="text-2xl font-bold font-mono text-emerald-400 mt-1">
          95.0%
        </div>
        <div class="text-[11px] text-text-muted mt-1">Organizational minimum SLA standard</div>
      </div>
    </div>

    <!-- Live Escalation Watchlist Table -->
    <div class="bg-bg-surface border border-border-default rounded-lg overflow-hidden shadow-sm">
      <div class="px-5 py-4 border-b border-border-default flex items-center justify-between">
        <h2 class="text-sm font-semibold text-text-primary uppercase tracking-wider">
          Tickets Requiring Immediate Attention
        </h2>
        <span class="text-xs text-text-muted font-mono font-normal">
          {{ slaStore.atRiskTickets.length }} tickets in watchlist
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-bg-surface-raised border-b border-border-subtle text-text-muted font-mono uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3">Code</th>
              <th class="px-4 py-3">Severity</th>
              <th class="px-4 py-3">Incident Title</th>
              <th class="px-4 py-3">Requester</th>
              <th class="px-4 py-3">Assignee</th>
              <th class="px-4 py-3">Deadline Status</th>
              <th class="px-4 py-3 text-right">Escalate</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle">
            <tr v-if="slaStore.isLoading">
              <td colspan="7" class="px-4 py-12 text-center text-text-muted font-mono">
                Loading SLA watchlist...
              </td>
            </tr>

            <tr v-else-if="slaStore.atRiskTickets.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-text-muted">
                <CheckCircle2 class="w-8 h-8 text-emerald-400 mx-auto mb-2" />
                <div class="text-sm font-semibold text-text-primary">No Breached or At-Risk Incidents</div>
                <p class="text-xs text-text-muted mt-1">All current open requests are operating within SLA commitments.</p>
              </td>
            </tr>

            <tr
              v-for="t in slaStore.atRiskTickets"
              :key="t.id"
              class="hover:bg-bg-surface-hover/50 transition-subtle"
              :class="t.is_sla_breached ? 'bg-red-950/10' : ''"
            >
              <td class="px-4 py-3 font-mono font-semibold text-blue-400 whitespace-nowrap">
                <RouterLink :to="`/tickets/${t.id}`" class="hover:underline">
                  {{ t.reference_no }}
                </RouterLink>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <PriorityBadge :priority="t.priority" />
              </td>
              <td class="px-4 py-3 max-w-sm truncate text-text-primary font-medium">
                {{ t.title }}
              </td>
              <td class="px-4 py-3 text-text-secondary whitespace-nowrap">
                {{ t.requester?.name }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span v-if="t.agent" class="text-text-primary font-medium">{{ t.agent.name }}</span>
                <span v-else class="text-red-400 font-mono font-bold">UNASSIGNED</span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <SlaCountdown
                  :resolution-due-at="t.resolution_due_at"
                  :resolved-at="t.resolved_at"
                  :is-breached="t.is_sla_breached"
                  :is-at-risk="t.is_at_risk"
                />
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <RouterLink
                  :to="`/tickets/${t.id}`"
                  class="px-2.5 py-1 rounded bg-blue-600 hover:bg-blue-500 text-white font-mono text-xs transition-subtle"
                >
                  Resolve Now &rarr;
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
import { computed, onMounted } from 'vue'
import { useSlaStore } from '@/stores/sla'
import PriorityBadge from '@/components/common/PriorityBadge.vue'
import SlaCountdown from '@/components/common/SlaCountdown.vue'
import { CheckCircle2 } from 'lucide-vue-next'

const slaStore = useSlaStore()

const breachedCount = computed(() => {
  return slaStore.atRiskTickets.filter((t) => t.is_sla_breached).length
})

const atRiskCount = computed(() => {
  return slaStore.atRiskTickets.filter((t) => !t.is_sla_breached && t.is_at_risk).length
})

async function reloadBoard() {
  await slaStore.fetchAtRiskTickets(slaStore.thresholdMinutes)
}

onMounted(async () => {
  await reloadBoard()
})
</script>
