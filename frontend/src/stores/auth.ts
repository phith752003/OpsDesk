import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/services/authApi'
import type { User, UserRole } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('opsdesk_token'))
  const user = ref<User | null>(
    localStorage.getItem('opsdesk_user') ? JSON.parse(localStorage.getItem('opsdesk_user')!) : null
  )
  const meta = ref<{
    categories: any[]
    departments: any[]
    priorities: Array<{ value: string; label: string }>
    statuses: Array<{ value: string; label: string }>
    roles: Array<{ value: string; label: string }>
  } | null>(null)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const role = computed<UserRole | undefined>(() => user.value?.role)
  const isAdmin = computed(() => role.value === 'admin')
  const isAgent = computed(() => role.value === 'agent')
  const isRequester = computed(() => role.value === 'requester')
  const isManager = computed(() => role.value === 'manager')
  const isStaff = computed(() => ['admin', 'agent', 'manager'].includes(role.value || ''))

  async function login(email: string, password: string) {
    isLoading.value = true
    try {
      const data = await authApi.login({ email, password })
      token.value = data.token
      user.value = data.user
      localStorage.setItem('opsdesk_token', data.token)
      localStorage.setItem('opsdesk_user', JSON.stringify(data.user))
      await fetchMeta()
      return data.user
    } finally {
      isLoading.value = false
    }
  }

  async function switchDemoUser(roleName: 'admin' | 'agent' | 'requester' | 'manager') {
    const emailMap = {
      admin: 'admin@opsdesk.local',
      agent: 'agent@opsdesk.local',
      requester: 'requester@opsdesk.local',
      manager: 'manager@opsdesk.local',
    }
    return login(emailMap[roleName], 'Password123!')
  }

  async function fetchMe() {
    if (!token.value) return null
    try {
      const data = await authApi.me()
      user.value = data.user
      localStorage.setItem('opsdesk_user', JSON.stringify(data.user))
      return data.user
    } catch {
      logout()
      return null
    }
  }

  async function fetchMeta() {
    try {
      meta.value = await authApi.getMeta()
    } catch (e) {
      console.error('Failed to load metadata', e)
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await authApi.logout()
      }
    } catch {
      // Ignore network errors on logout
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('opsdesk_token')
      localStorage.removeItem('opsdesk_user')
    }
  }

  return {
    token,
    user,
    meta,
    isLoading,
    isAuthenticated,
    role,
    isAdmin,
    isAgent,
    isRequester,
    isManager,
    isStaff,
    login,
    switchDemoUser,
    fetchMe,
    fetchMeta,
    logout,
  }
})
