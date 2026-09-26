<template>
  <span
    :class="[
      'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border uppercase tracking-wider',
      priorityClasses
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', dotClasses]" />
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
      return 'bg-red-950/60 text-red-300 border-red-500/40'
    case 'high':
      return 'bg-orange-950/50 text-orange-300 border-orange-500/30'
    case 'medium':
      return 'bg-amber-950/40 text-amber-300 border-amber-500/30'
    case 'low':
      return 'bg-emerald-950/40 text-emerald-300 border-emerald-500/30'
    default:
      return 'bg-gray-800 text-gray-400 border-gray-700'
  }
})

const dotClasses = computed(() => {
  switch (props.priority) {
    case 'critical':
      return 'bg-red-500 animate-pulse'
    case 'high':
      return 'bg-orange-400'
    case 'medium':
      return 'bg-amber-400'
    case 'low':
      return 'bg-emerald-400'
    default:
      return 'bg-gray-400'
  }
})
</script>
