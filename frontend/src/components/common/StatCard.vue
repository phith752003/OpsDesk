<template>
  <div class="p-5 bg-bg-surface border border-border-default rounded-lg transition-subtle hover:border-border-strong shadow-sm">
    <div class="flex items-center justify-between">
      <span class="text-xs font-medium text-text-secondary uppercase tracking-wider">{{ title }}</span>
      <div v-if="$slots.icon" class="p-2 rounded bg-bg-surface-raised border border-border-subtle text-text-muted">
        <slot name="icon" />
      </div>
    </div>
    <div class="mt-3 flex items-baseline justify-between">
      <div class="text-2xl font-bold font-mono tabular-nums text-text-primary tracking-tight">
        {{ value }}
      </div>
      <div v-if="change" :class="['text-xs font-medium', isPositive ? 'text-emerald-400' : 'text-amber-400']">
        {{ change }}
      </div>
    </div>
    <div v-if="subtitle" class="mt-1 text-xs text-text-muted">
      {{ subtitle }}
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string
  value: string | number
  subtitle?: string
  change?: string
  isPositive?: boolean
}>()
</script>
