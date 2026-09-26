import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/tickets',
    name: 'Tickets',
    component: () => import('@/views/TicketListView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/tickets/new',
    name: 'CreateTicket',
    component: () => import('@/views/TicketFormView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/tickets/:id',
    name: 'TicketDetail',
    component: () => import('@/views/TicketDetailView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/sla',
    name: 'SlaMonitoring',
    component: () => import('@/views/SlaMonitoringView.vue'),
    meta: { requiresAuth: true, requiresStaff: true },
  },
  {
    path: '/workload',
    name: 'AgentWorkload',
    component: () => import('@/views/AgentWorkloadView.vue'),
    meta: { requiresAuth: true, requiresManager: true },
  },
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('@/views/ReportsView.vue'),
    meta: { requiresAuth: true, requiresManager: true },
  },
  {
    path: '/users',
    name: 'UserManagement',
    component: () => import('@/views/UserManagementView.vue'),
    meta: { requiresAuth: true, requiresManager: true },
  },
  {
    path: '/settings',
    name: 'SystemSettings',
    component: () => import('@/views/SystemSettingsView.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()

  // Initialize session from token if not yet loaded
  if (authStore.token && !authStore.user) {
    await authStore.fetchMe()
  }

  const isAuth = authStore.isAuthenticated

  if (to.meta.requiresAuth && !isAuth) {
    return next({ name: 'Login' })
  }

  if (to.meta.guestOnly && isAuth) {
    return next({ name: 'Dashboard' })
  }

  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next({ name: 'Dashboard' })
  }

  if (to.meta.requiresManager && !(authStore.isAdmin || authStore.isManager)) {
    return next({ name: 'Dashboard' })
  }

  if (to.meta.requiresStaff && !authStore.isStaff) {
    return next({ name: 'Dashboard' })
  }

  next()
})

export default router
