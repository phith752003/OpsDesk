import { defineStore } from 'pinia'
import { ref } from 'vue'
import { slaApi } from '@/services/slaApi'
import type { SlaPolicy, Ticket } from '@/types'

export const useSlaStore = defineStore('sla', () => {
  const policies = ref<SlaPolicy[]>([])
  const atRiskTickets = ref<Ticket[]>([])
  const thresholdMinutes = ref(30)
  const isLoading = ref(false)

  async function fetchPolicies() {
    isLoading.value = true
    try {
      const res = await slaApi.getPolicies()
      policies.value = res.policies
    } finally {
      isLoading.value = false
    }
  }

  async function fetchAtRiskTickets(threshold: number = 30) {
    isLoading.value = true
    try {
      const res = await slaApi.getAtRiskTickets(threshold)
      atRiskTickets.value = res.tickets
      thresholdMinutes.value = res.threshold_minutes
    } finally {
      isLoading.value = false
    }
  }

  async function updatePolicy(id: number, payload: { response_time_minutes?: number; resolution_time_minutes?: number }) {
    const res = await slaApi.updatePolicy(id, payload)
    const idx = policies.value.findIndex((p) => p.id === id)
    if (idx !== -1) {
      policies.value[idx] = res.policy
    }
    return res.policy
  }

  return {
    policies,
    atRiskTickets,
    thresholdMinutes,
    isLoading,
    fetchPolicies,
    fetchAtRiskTickets,
    updatePolicy,
  }
})
