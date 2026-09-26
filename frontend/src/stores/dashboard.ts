import { defineStore } from 'pinia'
import { ref } from 'vue'
import { reportApi } from '@/services/reportApi'
import type { DashboardMetrics, AgentWorkload, ReportsTrends } from '@/types'

export const useDashboardStore = defineStore('dashboard', () => {
  const metrics = ref<DashboardMetrics | null>(null)
  const workload = ref<AgentWorkload[]>([])
  const trends = ref<ReportsTrends | null>(null)
  const settings = ref<Record<string, any>>({})
  const isLoading = ref(false)

  async function fetchDashboardMetrics() {
    isLoading.value = true
    try {
      metrics.value = await reportApi.getDashboardMetrics()
    } finally {
      isLoading.value = false
    }
  }

  async function fetchWorkload() {
    isLoading.value = true
    try {
      const res = await reportApi.getWorkload()
      workload.value = res.workload
    } finally {
      isLoading.value = false
    }
  }

  async function fetchTrends() {
    isLoading.value = true
    try {
      trends.value = await reportApi.getTrends()
    } finally {
      isLoading.value = false
    }
  }

  async function fetchSettings() {
    try {
      const res = await reportApi.getSettings()
      settings.value = res.settings
    } catch (e) {
      console.error('Failed to load settings', e)
    }
  }

  async function updateSettings(newSettings: Record<string, any>) {
    const res = await reportApi.updateSettings(newSettings)
    settings.value = res.settings
    return res.settings
  }

  return {
    metrics,
    workload,
    trends,
    settings,
    isLoading,
    fetchDashboardMetrics,
    fetchWorkload,
    fetchTrends,
    fetchSettings,
    updateSettings,
  }
})
