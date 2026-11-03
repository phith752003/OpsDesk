<template>
  <div class="inline-flex items-center text-xs tabular-nums font-mono">
    <!-- Breached State -->
    <span
      v-if="isBreached"
      class="inline-flex items-center px-2 py-0.5 rounded bg-red-50 text-red-700 border border-red-300 font-semibold"
      title="SLA deadline has been breached"
    >
      <AlertTriangle class="w-3 h-3 mr-1 text-red-600 shrink-0" />
      SLA Breached
    </span>

    <!-- Completed within SLA -->
    <span
      v-else-if="isCompleted"
      class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-300 font-medium"
    >
      <CheckCircle2 class="w-3 h-3 mr-1 text-emerald-600 shrink-0" />
      Met SLA
    </span>

    <!-- Active At Risk (<30 mins) -->
    <span
      v-else-if="isAtRisk"
      class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-300 font-bold animate-pulse"
      title="Resolution deadline approaching rapidly"
    >
      <Clock class="w-3 h-3 mr-1 text-amber-600 shrink-0" />
      {{ timeLabel }}
    </span>

    <!-- Active Healthy (>30 mins) -->
    <span
      v-else-if="resolutionDueAt"
      class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-300 font-medium"
    >
      <Clock class="w-3 h-3 mr-1 text-slate-500 shrink-0" />
      {{ timeLabel }}
    </span>

    <span v-else class="text-text-muted">
      —
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { AlertTriangle, CheckCircle2, Clock } from 'lucide-vue-next'

const props = defineProps<{
  resolutionDueAt?: string | null
  resolvedAt?: string | null
  isBreached?: boolean
  isAtRisk?: boolean
}>()

const isCompleted = computed(() => !!props.resolvedAt)

const timeLabel = computed(() => {
  if (!props.resolutionDueAt) return '—'
  const target = new Date(props.resolutionDueAt).getTime()
  const now = Date.now()
  const diffMinutes = Math.round((target - now) / 60000)

  if (diffMinutes < 0) {
    return `${Math.abs(diffMinutes)}m overdue`
  }

  if (diffMinutes < 60) {
    return `${diffMinutes}m left`
  }

  const hours = Math.floor(diffMinutes / 60)
  const mins = diffMinutes % 60
  return `${hours}h ${mins}m left`
})
</script>
