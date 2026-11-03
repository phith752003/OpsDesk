<template>
  <span
    :class="[
      'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border uppercase tracking-wider font-mono',
      priorityClasses
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full mr-1.5 shrink-0', dotClasses]" />
    {{ priority }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { TicketPriority } from '@/types'

const props = defineProps<{
  priority: TicketPriority | string
}>()

const priorityClasses = computed(() => {
  switch (props.priority) {
    case 'critical':
      return 'bg-red-50 text-red-700 border-red-300'
    case 'high':
      return 'bg-orange-50 text-orange-700 border-orange-300'
    case 'medium':
      return 'bg-amber-50 text-amber-800 border-amber-300'
    case 'low':
      return 'bg-emerald-50 text-emerald-700 border-emerald-300'
    default:
      return 'bg-slate-100 text-slate-700 border-slate-300'
  }
})

const dotClasses = computed(() => {
  switch (props.priority) {
    case 'critical':
      return 'bg-red-600 animate-pulse'
    case 'high':
      return 'bg-orange-500'
    case 'medium':
      return 'bg-amber-500'
    case 'low':
      return 'bg-emerald-500'
    default:
      return 'bg-slate-400'
  }
})
</script>
