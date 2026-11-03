<template>
  <span
    :class="[
      'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border font-mono',
      statusClasses
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full mr-1.5 shrink-0', dotClasses]" />
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
      return 'bg-blue-50 text-blue-700 border-blue-200'
    case 'assigned':
      return 'bg-indigo-50 text-indigo-700 border-indigo-200'
    case 'in_progress':
      return 'bg-sky-50 text-sky-700 border-sky-300'
    case 'pending':
      return 'bg-slate-100 text-slate-700 border-slate-300'
    case 'resolved':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'closed':
      return 'bg-gray-100 text-gray-600 border-gray-300'
    default:
      return 'bg-slate-100 text-slate-600 border-slate-300'
  }
})

const dotClasses = computed(() => {
  switch (props.status) {
    case 'open':
      return 'bg-blue-600'
    case 'assigned':
      return 'bg-indigo-600'
    case 'in_progress':
      return 'bg-sky-600 animate-pulse'
    case 'pending':
      return 'bg-slate-500'
    case 'resolved':
      return 'bg-emerald-600'
    case 'closed':
      return 'bg-gray-500'
    default:
      return 'bg-slate-400'
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
