<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-text-primary">
          Support Specialist Workload &amp; Capacity
        </h1>
        <p class="text-xs text-text-muted mt-0.5 font-mono">
          Real-time active assignment distribution across IT tiers
        </p>
      </div>
      <div class="text-xs text-text-muted font-mono bg-bg-surface px-3 py-1.5 rounded border border-border-default">
        Active Agents: <span class="text-text-primary font-bold">{{ dashboardStore.workload.length }}</span>
      </div>
    </div>

    <!-- Agent Capacity Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="agent in dashboardStore.workload"
        :key="agent.agent_id"
        class="bg-bg-surface border border-border-default rounded-lg p-5 space-y-4 hover:border-border-strong transition-subtle shadow-sm"
      >
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-sm font-bold text-text-primary">{{ agent.name }}</h3>
            <span class="text-[11px] text-text-muted font-mono">{{ agent.department }}</span>
          </div>
          <span
            class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold"
            :class="agent.capacity_percentage > 80 ? 'bg-red-950 text-red-300 border border-red-500/40' : agent.capacity_percentage > 50 ? 'bg-amber-950 text-amber-300 border border-amber-500/40' : 'bg-emerald-950 text-emerald-300 border border-emerald-500/40'"
          >
            {{ agent.capacity_percentage }}% Load
          </span>
        </div>

        <!-- Capacity Bar -->
        <div class="space-y-1">
          <div class="w-full h-2 rounded-full bg-bg-base overflow-hidden border border-border-subtle">
            <div
              class="h-full rounded-full transition-all duration-300"
              :class="agent.capacity_percentage > 80 ? 'bg-red-500' : agent.capacity_percentage > 50 ? 'bg-amber-500' : 'bg-emerald-500'"
              :style="{ width: `${agent.capacity_percentage}%` }"
            />
          </div>
          <div class="flex justify-between text-[10px] text-text-muted font-mono">
            <span>{{ agent.active_tickets_count }} Active Tickets</span>
            <span>Max Benchmark: 10</span>
          </div>
        </div>

        <!-- Workload Stats Matrix -->
        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-border-subtle text-center text-xs">
          <div class="p-2 rounded bg-bg-surface-raised border border-border-subtle">
            <div class="text-[10px] text-text-muted font-mono uppercase">In Prog</div>
            <div class="text-sm font-bold font-mono text-blue-400 mt-0.5">{{ agent.in_progress_count }}</div>
          </div>
          <div class="p-2 rounded bg-bg-surface-raised border border-border-subtle">
            <div class="text-[10px] text-text-muted font-mono uppercase">Resolved</div>
            <div class="text-sm font-bold font-mono text-emerald-400 mt-0.5">{{ agent.resolved_today_count }}</div>
          </div>
          <div class="p-2 rounded bg-bg-surface-raised border border-border-subtle">
            <div class="text-[10px] text-text-muted font-mono uppercase">Breaches</div>
            <div class="text-sm font-bold font-mono text-red-400 mt-0.5">{{ agent.breached_count }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'

const dashboardStore = useDashboardStore()

onMounted(async () => {
  await dashboardStore.fetchWorkload()
})
</script>
