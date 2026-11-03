<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-border-default">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-text-primary">
          System Configuration &amp; SLA Policies
        </h1>
        <p class="text-xs text-text-muted mt-0.5 font-mono">
          Global helpdesk rules, response targets, and institutional parameters
        </p>
      </div>
      <div v-if="successMsg" class="px-3 py-1.5 rounded bg-emerald-50 border border-emerald-300 text-emerald-700 text-xs font-mono font-medium">
        {{ successMsg }}
      </div>
    </div>

    <!-- SLA Policies Matrix Section -->
    <div class="bg-bg-surface border border-border-default rounded-lg p-6 space-y-4 shadow-sm">
      <div class="flex items-center justify-between border-b border-border-subtle pb-3">
        <div>
          <h2 class="text-sm font-bold text-text-primary uppercase tracking-wider">
            SLA Policy Deadlines by Severity
          </h2>
          <p class="text-xs text-text-muted mt-0.5 font-mono">
            Defines initial response and resolution windows for ticket timers
          </p>
        </div>
        <Shield class="w-5 h-5 text-blue-600" />
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="text-text-muted border-b border-border-subtle">
            <tr>
              <th class="py-2.5">Priority Tier</th>
              <th class="py-2.5">Response Target</th>
              <th class="py-2.5">Resolution Target</th>
              <th class="py-2.5 text-right">Default Policy</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle">
            <tr v-for="policy in slaStore.policies" :key="policy.id">
              <td class="py-3 font-bold uppercase text-text-primary">
                {{ policy.priority }}
              </td>
              <td class="py-3 text-text-secondary">
                {{ formatMinutes(policy.response_time_minutes) }}
                <span class="text-text-muted text-[10px]">({{ policy.response_time_minutes }}m)</span>
              </td>
              <td class="py-3 text-text-secondary">
                {{ formatMinutes(policy.resolution_time_minutes) }}
                <span class="text-text-muted text-[10px]">({{ policy.resolution_time_minutes }}m)</span>
              </td>
              <td class="py-3 text-right">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] bg-blue-50 text-blue-700 border border-blue-200 font-semibold">
                  Active
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Global Parameters Form -->
    <form @submit.prevent="saveSettings" class="bg-bg-surface border border-border-default rounded-lg p-6 space-y-4 shadow-sm">
      <h2 class="text-sm font-bold text-text-primary uppercase tracking-wider border-b border-border-subtle pb-3">
        Institutional Helpdesk Settings
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <div>
          <label class="block font-medium text-text-secondary mb-1">Organization Title</label>
          <input
            v-model="localSettings.app_organization"
            type="text"
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500 font-mono"
          />
        </div>

        <div>
          <label class="block font-medium text-text-secondary mb-1">SLA Warning Window (Minutes)</label>
          <input
            v-model.number="localSettings.sla_warning_threshold_minutes"
            type="number"
            min="5"
            max="120"
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500 font-mono"
          />
          <span class="text-[10px] text-text-muted mt-1 block">Triggers amber highlight before deadline</span>
        </div>

        <div>
          <label class="block font-medium text-text-secondary mb-1">Default Incident Priority</label>
          <select
            v-model="localSettings.default_ticket_priority"
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500 font-mono"
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
          </select>
        </div>

        <div>
          <label class="block font-medium text-text-secondary mb-1">Public Self-Registration</label>
          <select
            v-model="localSettings.allow_public_registration"
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-text-primary focus:outline-none focus:border-blue-500 font-mono"
          >
            <option :value="false">Disabled (Corporate Directory Only)</option>
            <option :value="true">Enabled</option>
          </select>
        </div>
      </div>

      <div class="pt-4 border-t border-border-default flex justify-end">
        <button
          type="submit"
          :disabled="isSaving"
          class="px-5 py-2 rounded bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-sm transition-subtle disabled:opacity-50"
        >
          {{ isSaving ? 'Saving Settings...' : 'Save Configuration' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useSlaStore } from '@/stores/sla'
import { useDashboardStore } from '@/stores/dashboard'
import { Shield } from 'lucide-vue-next'

const slaStore = useSlaStore()
const dashboardStore = useDashboardStore()

const isSaving = ref(false)
const successMsg = ref('')

const localSettings = ref({
  app_organization: 'Acme Global Operations',
  sla_warning_threshold_minutes: 30,
  default_ticket_priority: 'medium',
  allow_public_registration: false,
})

function formatMinutes(minutes: number): string {
  if (minutes < 60) return `${minutes} Minutes`
  const hours = minutes / 60
  if (hours < 24) return `${hours} Hours`
  const days = hours / 24
  return `${days} Days`
}

async function saveSettings() {
  isSaving.value = true
  successMsg.value = ''
  try {
    await dashboardStore.updateSettings(localSettings.value)
    successMsg.value = 'Configuration successfully saved.'
    setTimeout(() => {
      successMsg.value = ''
    }, 3000)
  } catch (e) {
    console.error('Failed to save settings', e)
  } finally {
    isSaving.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    slaStore.fetchPolicies(),
    dashboardStore.fetchSettings(),
  ])
  if (dashboardStore.settings) {
    Object.assign(localSettings.value, dashboardStore.settings)
  }
})
</script>

