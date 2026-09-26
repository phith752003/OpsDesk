<template>
  <div class="inline-flex items-center text-xs tabular-nums font-mono">
    <!-- Breached State -->
    <span
      v-if="isBreached"
      class="inline-flex items-center px-2 py-0.5 rounded bg-red-950/80 text-red-300 border border-red-500/50 font-medium"
      title="SLA deadline has been breached"
    >
      <AlertTriangle class="w-3 h-3 mr-1 text-red-400" />
      SLA Breached
    </span>

    <!-- Completed within SLA -->
    <span
      v-else-if="isCompleted"
      class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-950/60 text-emerald-300 border border-emerald-500/30"
    >
      <CheckCircle2 class="w-3 h-3 mr-1 text-emerald-400" />
      Met SLA
    </span>

    <!-- Active At Risk (<30 mins) -->
    <span
      v-else-if="isAtRisk"
      class="inline-flex items-center px-2 py-0.5 rounded bg-amber-950/80 text-amber-300 border border-amber-500/50 font-semibold animate-pulse"
      title="Resolution deadline approaching rapidly"
    >
      <Clock class="w-3 h-3 mr-1 text-amber-400" />
      {{ timeLabel }}
    </span>

    <!-- Active Healthy (>30 mins) -->
    <span
      v-else-if="resolutionDueAt"
      class="inline-flex items-center px-2 py-0.5 rounded bg-gray-800 text-gray-300 border border-gray-700"
    >
      <Clock class="w-3 h-3 mr-1 text-gray-400" />
      {{ timeLabel }}
    </span>

    <span v-else class="text-gray-500">
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
