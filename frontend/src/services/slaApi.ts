import api from './api'
import type { SlaPolicy, Ticket } from '@/types'

export const slaApi = {
  async getPolicies(): Promise<{ policies: SlaPolicy[] }> {
    const res = await api.get('/sla/policies')
    return res.data
  },

  async getAtRiskTickets(threshold: number = 30): Promise<{ tickets: Ticket[]; threshold_minutes: number }> {
    const res = await api.get('/sla/at-risk', { params: { threshold } })
    return res.data
  },

  async updatePolicy(
    id: number,
    payload: { response_time_minutes?: number; resolution_time_minutes?: number }
  ): Promise<{ policy: SlaPolicy; message: string }> {
    const res = await api.put(`/sla/policies/${id}`, payload)
    return res.data
  },
}
