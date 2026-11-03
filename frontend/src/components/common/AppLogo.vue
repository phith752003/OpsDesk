<template>
  <div class="inline-flex items-center space-x-2.5 select-none">
    <!-- Geometric SVG Mark (No text inside) -->
    <svg
      :class="[sizeClasses, 'shrink-0 transition-subtle']"
      viewBox="0 0 48 48"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      role="img"
      aria-label="OpsDesk Emblem"
    >
      <defs>
        <linearGradient :id="`logo-bg-${uniqueId}`" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#141B2D"/>
          <stop offset="100%" stop-color="#0B0F17"/>
        </linearGradient>
        <linearGradient :id="`plate-top-${uniqueId}`" x1="10" y1="7" x2="38" y2="23" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#60A5FA"/>
          <stop offset="100%" stop-color="#2563EB"/>
        </linearGradient>
        <linearGradient :id="`plate-left-${uniqueId}`" x1="10" y1="18" x2="22" y2="39" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#2563EB"/>
          <stop offset="100%" stop-color="#1E3A8A"/>
        </linearGradient>
        <linearGradient :id="`plate-right-${uniqueId}`" x1="26" y1="18" x2="38" y2="39" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#3B82F6"/>
          <stop offset="100%" stop-color="#1D4ED8"/>
        </linearGradient>
      </defs>

      <!-- Background Bevel Container -->
      <rect
        width="48"
        height="48"
        rx="11"
        :fill="`url(#logo-bg-${uniqueId})`"
        stroke="#CBD5E1"
        stroke-width="1"
      />

      <!-- Top Prism Plate -->
      <path
        d="M 24 8.5 L 37.5 16.2 L 24 23 L 10.5 16.2 Z"
        :fill="`url(#plate-top-${uniqueId})`"
        stroke="#93C5FD"
        stroke-width="0.5"
        stroke-opacity="0.4"
      />

      <!-- Left Isometric Plate -->
      <path
        d="M 10 18.5 L 22.5 24.8 L 22.5 38.5 L 10 32 Z"
        :fill="`url(#plate-left-${uniqueId})`"
        stroke="#60A5FA"
        stroke-width="0.5"
        stroke-opacity="0.3"
      />

      <!-- Right Isometric Plate -->
      <path
        d="M 25.5 24.8 L 38 18.5 L 38 32 L 25.5 38.5 Z"
        :fill="`url(#plate-right-${uniqueId})`"
        stroke="#93C5FD"
        stroke-width="0.5"
        stroke-opacity="0.4"
      />

      <!-- Central Operational Command Core (Cyan Node) -->
      <circle cx="24" cy="24" r="2.2" fill="#38BDF8" />
      <circle cx="24" cy="24" r="1.1" fill="#FFFFFF" />
    </svg>

    <!-- Optional Wordmark -->
    <div v-if="showWordmark" class="flex flex-col">
      <span class="text-sm font-bold tracking-tight text-text-primary leading-tight">OpsDesk</span>
      <span class="text-[9px] text-text-muted font-mono tracking-widest uppercase">Operations Control</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    size?: 'sm' | 'md' | 'lg' | 'xl'
    showWordmark?: boolean
  }>(),
  {
    size: 'md',
    showWordmark: false,
  }
)

const uniqueId = Math.random().toString(36).substring(2, 9)

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-6 h-6'
    case 'lg':
      return 'w-12 h-12'
    case 'xl':
      return 'w-16 h-16'
    case 'md':
    default:
      return 'w-8 h-8'
  }
})
</script>

