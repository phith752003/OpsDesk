<template>
  <div class="min-h-screen bg-bg-base text-text-primary">
    <!-- Unauthenticated views (Login) -->
    <template v-if="isGuestView">
      <RouterView />
    </template>

    <!-- Authenticated views with AppLayout -->
    <template v-else>
      <AppLayout>
        <RouterView />
      </AppLayout>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'

const route = useRoute()

const isGuestView = computed(() => {
  return route.name === 'Login'
})
</script>
