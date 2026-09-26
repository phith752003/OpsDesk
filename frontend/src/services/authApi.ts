import api from './api'
import type { User } from '@/types'

export interface LoginResponse {
  token: string
  user: User
}

export const authApi = {
  async login(credentials: { email: string; password: string }): Promise<LoginResponse> {
    const res = await api.post<LoginResponse>('/auth/login', credentials)
    return res.data
  },

  async me(): Promise<{ user: User }> {
    const res = await api.get<{ user: User }>('/auth/me')
    return res.data
  },

  async logout(): Promise<void> {
    await api.post('/auth/logout')
  },

  async getMeta(): Promise<{
    categories: any[]
    departments: any[]
    priorities: Array<{ value: string; label: string }>
    statuses: Array<{ value: string; label: string }>
    roles: Array<{ value: string; label: string }>
  }> {
    const res = await api.get('/meta')
    return res.data
  },
}
