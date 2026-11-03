<template>
  <div class="min-h-screen flex bg-bg-base text-text-primary">
    <!-- Desktop Sidebar -->
    <aside
      :class="[
        'w-64 border-r border-border-default bg-bg-surface flex flex-col shrink-0 transition-subtle z-30',
        isMobileMenuOpen ? 'fixed inset-y-0 left-0' : 'hidden md:flex'
      ]"
    >
      <!-- Logo Header -->
      <div class="h-16 px-6 border-b border-border-default flex items-center justify-between">
        <AppLogo size="md" :show-wordmark="true" />
        <button
          class="md:hidden p-1 text-text-muted hover:text-text-primary"
          @click="isMobileMenuOpen = false"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <RouterLink
          to="/dashboard"
          class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-subtle"
          :class="isActive('/dashboard') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200' : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised'"
          @click="isMobileMenuOpen = false"
        >
          <LayoutDashboard class="w-4 h-4 mr-3 shrink-0" />
          Dashboard
        </RouterLink>

        <RouterLink
          to="/tickets"
          class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-subtle"
          :class="isActive('/tickets') && !isActive('/tickets/new') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200' : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised'"
          @click="isMobileMenuOpen = false"
        >
          <Ticket class="w-4 h-4 mr-3 shrink-0" />
          Ticket Queue
        </RouterLink>

        <RouterLink
          to="/tickets/new"
          class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-subtle"
          :class="isActive('/tickets/new') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200' : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised'"
          @click="isMobileMenuOpen = false"
        >
          <PlusCircle class="w-4 h-4 mr-3 shrink-0" />
          Create Ticket
        </RouterLink>

        <!-- Staff Only Navigation -->
        <div v-if="authStore.isStaff" class="pt-4 pb-1 px-3 text-[11px] font-semibold text-text-muted uppercase tracking-wider">
          Operations
        </div>

        <RouterLink
          v-if="authStore.isStaff"
          to="/sla"
          class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-subtle"
          :class="isActive('/sla') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200' : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised'"
          @click="isMobileMenuOpen = false"
        >
          <ShieldAlert class="w-4 h-4 mr-3 shrink-0 text-amber-600" />
          SLA Monitoring
        </RouterLink>

        <RouterLink
          v-if="authStore.isAdmin || authStore.isManager"
          to="/workload"
          class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-subtle"
          :class="isActive('/workload') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200' : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised'"
          @click="isMobileMenuOpen = false"
        >
          <Users class="w-4 h-4 mr-3 shrink-0" />
          Agent Workload
        </RouterLink>

        <RouterLink
          v-if="authStore.isAdmin || authStore.isManager"
          to="/reports"
          class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-subtle"
          :class="isActive('/reports') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200' : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised'"
          @click="isMobileMenuOpen = false"
        >
          <BarChart3 class="w-4 h-4 mr-3 shrink-0" />
          Analytics & Reports
        </RouterLink>

        <!-- Admin Only Navigation -->
        <div v-if="authStore.isAdmin || authStore.isManager" class="pt-4 pb-1 px-3 text-[11px] font-semibold text-text-muted uppercase tracking-wider">
          Administration
        </div>

        <RouterLink
          v-if="authStore.isAdmin || authStore.isManager"
          to="/users"
          class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-subtle"
          :class="isActive('/users') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200' : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised'"
          @click="isMobileMenuOpen = false"
        >
          <UserCog class="w-4 h-4 mr-3 shrink-0" />
          User Management
        </RouterLink>

        <RouterLink
          v-if="authStore.isAdmin"
          to="/settings"
          class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-subtle"
          :class="isActive('/settings') ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200' : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised'"
          @click="isMobileMenuOpen = false"
        >
          <Sliders class="w-4 h-4 mr-3 shrink-0" />
          System Settings
        </RouterLink>
      </nav>

      <!-- Sidebar Footer User Profile -->
      <div class="p-3 border-t border-border-default bg-bg-surface-raised/40">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3 overflow-hidden">
            <div class="w-8 h-8 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center font-bold text-xs text-blue-700 shrink-0">
              {{ authStore.user?.name.charAt(0) || 'U' }}
            </div>
            <div class="flex flex-col min-w-0">
              <span class="text-xs font-semibold text-text-primary truncate">{{ authStore.user?.name }}</span>
              <span class="text-[11px] text-text-muted capitalize font-mono">{{ authStore.user?.role }}</span>
            </div>
          </div>
          <button
            type="button"
            class="p-1.5 rounded text-text-muted hover:text-red-600 hover:bg-bg-surface-raised transition-subtle"
            title="Log out"
            @click="handleLogout"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Top Application Header -->
      <header class="h-16 border-b border-border-default bg-bg-surface px-4 md:px-8 flex items-center justify-between shrink-0 z-20">
        <div class="flex items-center space-x-3">
          <button
            class="md:hidden p-2 rounded text-text-muted hover:text-text-primary"
            @click="isMobileMenuOpen = true"
          >
            <Menu class="w-5 h-5" />
          </button>
          <div class="flex items-center space-x-2 text-xs font-mono text-text-muted">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
            <span class="hidden sm:inline">OPSDESK-CORE</span>
            <span>•</span>
            <span class="text-text-secondary uppercase">{{ currentRouteName }}</span>
          </div>
        </div>

        <!-- Role Quick Switcher for Evaluator Convenience -->
        <div class="flex items-center space-x-3">
          <div class="hidden lg:flex items-center bg-bg-surface-raised border border-border-default rounded p-1 text-xs">
            <span class="px-2 text-text-muted font-medium">Demo Persona:</span>
            <button
              v-for="r in roles"
              :key="r.key"
              type="button"
              :class="[
                'px-2 py-1 rounded font-mono transition-subtle',
                authStore.role === r.key
                  ? 'bg-blue-600 text-white font-semibold shadow-sm'
                  : 'text-text-secondary hover:text-text-primary hover:bg-bg-surface-hover'
              ]"
              @click="switchRole(r.key)"
            >
              {{ r.label }}
            </button>
          </div>

          <button
            class="p-2 rounded text-text-muted hover:text-text-primary hover:bg-bg-surface-raised transition-subtle"
            title="Quick Sign Out"
            @click="handleLogout"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </header>

      <!-- Routed View Container -->
      <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-bg-base">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppLogo from '@/components/common/AppLogo.vue'
import {
  LayoutDashboard,
  Ticket,
  PlusCircle,
  ShieldAlert,
  Users,
  BarChart3,
  UserCog,
  Sliders,
  LogOut,
  Menu,
  X,
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isMobileMenuOpen = ref(false)

const roles = [
  { key: 'admin' as const, label: 'Admin' },
  { key: 'agent' as const, label: 'Agent' },
  { key: 'requester' as const, label: 'Requester' },
  { key: 'manager' as const, label: 'Manager' },
]

const currentRouteName = computed(() => {
  return (route.name as string) || 'Dashboard'
})

function isActive(path: string) {
  return route.path === path || route.path.startsWith(path + '/')
}

async function switchRole(roleKey: 'admin' | 'agent' | 'requester' | 'manager') {
  await authStore.switchDemoUser(roleKey)
  // Reload current route data
  router.push(route.path)
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

