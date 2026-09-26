<template>
  <span
    :class="[
      'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border',
      statusClasses
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', dotClasses]" />
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { TicketStatus } from '@/types'

const props = defineProps<{
  status: TicketStatus | string
}>()

const statusClasses = computed(() => {
  switch (props.status) {
    case 'open':
      return 'bg-slate-800 text-blue-300 border-blue-500/30'
    case 'assigned':
      return 'bg-indigo-950 text-indigo-300 border-indigo-500/30'
    case 'in_progress':
      return 'bg-blue-950 text-blue-400 border-blue-500/40'
    case 'pending':
      return 'bg-gray-800 text-gray-300 border-gray-600/40'
    case 'resolved':
      return 'bg-emerald-950 text-emerald-300 border-emerald-500/30'
    case 'closed':
      return 'bg-zinc-900 text-zinc-400 border-zinc-700/40'
    default:
      return 'bg-gray-800 text-gray-400 border-gray-700'
  }
})

const dotClasses = computed(() => {
  switch (props.status) {
    case 'open':
      return 'bg-blue-400'
    case 'assigned':
      return 'bg-indigo-400'
    case 'in_progress':
      return 'bg-blue-500 animate-pulse'
    case 'pending':
      return 'bg-gray-400'
    case 'resolved':
      return 'bg-emerald-400'
    case 'closed':
      return 'bg-zinc-500'
    default:
      return 'bg-gray-400'
  }
})

const label = computed(() => {
  const map: Record<string, string> = {
    open: 'Open',
    assigned: 'Assigned',
    in_progress: 'In Progress',
    pending: 'Pending Customer',
    resolved: 'Resolved',
    closed: 'Closed',
  }
  return map[props.status] || props.status
})
</script>
