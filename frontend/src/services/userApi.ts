import api from './api'
import type { User } from '@/types'

export const userApi = {
  async getUsers(params: { role?: string; search?: string; page?: number } = {}): Promise<{ data: User[]; meta: any }> {
    const res = await api.get('/users', { params })
    return {
      data: res.data.data || res.data,
      meta: res.data.meta,
    }
  },

  async getAgents(): Promise<{ agents: User[] }> {
    const res = await api.get('/users/agents')
    return res.data
  },

  async createUser(payload: {
    name: string
    email: string
    password: string
    role: string
    department_id?: number | null
  }): Promise<{ user: User; message: string }> {
    const res = await api.post('/users', payload)
    return res.data
  },

  async updateUser(
    id: number,
    payload: {
      name?: string
      email?: string
      password?: string
      role?: string
      department_id?: number | null
    }
  ): Promise<{ user: User; message: string }> {
    const res = await api.put(`/users/${id}`, payload)
    return res.data
  },

  async toggleActive(id: number): Promise<{ user: User; message: string }> {
    const res = await api.patch(`/users/${id}/toggle-active`)
    return res.data
  },
}
