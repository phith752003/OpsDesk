import api from './api'
import type { DashboardMetrics, AgentWorkload, ReportsTrends } from '@/types'

export const reportApi = {
  async getDashboardMetrics(): Promise<DashboardMetrics> {
    const res = await api.get<DashboardMetrics>('/dashboard/metrics')
    return res.data
  },

  async getWorkload(): Promise<{ workload: AgentWorkload[] }> {
    const res = await api.get('/reports/workload')
    return res.data
  },

  async getTrends(): Promise<ReportsTrends> {
    const res = await api.get<ReportsTrends>('/reports/trends')
    return res.data
  },

  async getSettings(): Promise<{ settings: Record<string, any> }> {
    const res = await api.get('/settings')
    return res.data
  },

  async updateSettings(settings: Record<string, any>): Promise<{ message: string; settings: Record<string, any> }> {
    const res = await api.put('/settings', { settings })
    return res.data
  },
}
