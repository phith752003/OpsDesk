<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-bg-base">
    <div class="w-full max-w-md">
      <!-- Brand Header -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-3">
          <AppLogo size="lg" />
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-text-primary">OpsDesk</h1>
        <p class="text-xs text-text-secondary mt-1 font-mono uppercase tracking-wider">
          IT Service Desk & Operations Management
        </p>
      </div>

      <!-- Login Card -->
      <div class="bg-bg-surface border border-border-default rounded-lg p-6 shadow-sm">
        <h2 class="text-base font-semibold text-text-primary mb-4">Sign in to your account</h2>

        <div v-if="errorMessage" class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-xs text-red-700 font-medium">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-text-secondary uppercase tracking-wider mb-1">
              Email Address
            </label>
            <input
              v-model="email"
              type="email"
              required
              class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-sm text-text-primary focus:outline-none focus:border-blue-500 font-mono"
              placeholder="agent@opsdesk.local"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-text-secondary uppercase tracking-wider mb-1">
              Password
            </label>
            <input
              v-model="password"
              type="password"
              required
              class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-sm text-text-primary focus:outline-none focus:border-blue-500"
              placeholder="••••••••••••"
            />
          </div>

          <button
            type="submit"
            :disabled="authStore.isLoading"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded transition-subtle disabled:opacity-50 flex items-center justify-center space-x-2 shadow-sm"
          >
            <span v-if="authStore.isLoading">Signing in...</span>
            <span v-else>Authenticate</span>
          </button>
        </form>

        <!-- 1-Click Demo Persona Switcher -->
        <div class="mt-6 pt-5 border-t border-border-default">
          <div class="text-[11px] font-semibold text-text-muted uppercase tracking-wider mb-2">
            Instant Demo Logins (Click to populate & sign in):
          </div>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="d in demoAccounts"
              :key="d.role"
              type="button"
              class="p-2 rounded bg-bg-surface-raised border border-border-default hover:border-blue-500 hover:bg-blue-50/50 text-left transition-subtle group"
              @click="quickLogin(d.role)"
            >
              <div class="text-xs font-semibold text-text-primary group-hover:text-blue-600">{{ d.name }}</div>
              <div class="text-[10px] text-text-muted font-mono capitalize">{{ d.role }}</div>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Context -->
      <div class="text-center mt-6 text-xs text-text-muted font-mono">
        Laravel 10 REST API &bull; Vue 3 &bull; Sanctum Auth
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLogo from '@/components/common/AppLogo.vue'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('agent@opsdesk.local')
const password = ref('Password123!')
const errorMessage = ref('')

const demoAccounts = [
  { role: 'admin' as const, name: 'Elena Rostova (Admin)' },
  { role: 'agent' as const, name: 'Alex Mercer (Support Agent)' },
  { role: 'requester' as const, name: 'Sarah Jenkins (Requester)' },
  { role: 'manager' as const, name: 'David Vance (Operations Mgr)' },
]

async function handleSubmit() {
  errorMessage.value = ''
  try {
    await authStore.login(email.value, password.value)
    router.push('/dashboard')
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Invalid credentials or connection error.'
  }
}

async function quickLogin(roleName: 'admin' | 'agent' | 'requester' | 'manager') {
  errorMessage.value = ''
  try {
    await authStore.switchDemoUser(roleName)
    router.push('/dashboard')
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Quick login failed.'
  }
}
</script>

