<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
      @click.self="close"
      @keydown.esc="close"
    >
      <div
        class="w-full max-w-lg bg-bg-surface border border-border-default rounded-lg shadow-2xl overflow-hidden transition-subtle"
        role="dialog"
        aria-modal="true"
      >
        <!-- Header -->
        <div class="px-6 py-4 border-b border-border-default flex items-center justify-between">
          <h3 class="text-base font-semibold text-text-primary">
            {{ title }}
          </h3>
          <button
            type="button"
            class="p-1 rounded text-text-muted hover:text-text-primary hover:bg-bg-surface-raised transition-subtle"
            @click="close"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Content -->
        <div class="p-6">
          <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer" class="px-6 py-3 bg-bg-surface-raised/50 border-t border-border-default flex items-center justify-end space-x-3">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { X } from 'lucide-vue-next'

defineProps<{
  isOpen: boolean
  title: string
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

function close() {
  emit('close')
}
</script>
