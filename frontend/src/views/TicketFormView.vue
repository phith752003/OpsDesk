<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-3 pb-4 border-b border-border-default">
      <RouterLink
        to="/tickets"
        class="p-1.5 rounded bg-bg-surface-raised border border-border-default hover:text-text-primary transition-subtle"
      >
        <ArrowLeft class="w-4 h-4" />
      </RouterLink>
      <div>
        <h1 class="text-xl font-bold tracking-tight text-text-primary">
          Submit IT Incident Request
        </h1>
        <p class="text-xs text-text-muted mt-0.5 font-mono">
          Provide complete diagnostics to accelerate triage &amp; resolution
        </p>
      </div>
    </div>

    <!-- Incident Form Card -->
    <form @submit.prevent="handleSubmit" class="bg-bg-surface border border-border-default rounded-lg p-6 space-y-5 shadow-sm">
      <div v-if="errorMessage" class="p-3 rounded bg-red-50 border border-red-200 text-xs text-red-700 font-medium">
        {{ errorMessage }}
      </div>

      <!-- Incident Title -->
      <div>
        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">
          Incident Summary / Title <span class="text-red-600">*</span>
        </label>
        <input
          v-model="form.title"
          type="text"
          required
          maxlength="255"
          placeholder="e.g. VPN tunnel disconnects repeatedly after 15 minutes"
          class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary placeholder:text-text-muted focus:outline-none focus:border-blue-500"
        />
        <span class="text-[10px] text-text-muted font-mono mt-1 block">
          Be specific. Include software names or hardware model numbers.
        </span>
      </div>

      <!-- Category Picker -->
      <div>
        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">
          Operational Domain / Category <span class="text-red-600">*</span>
        </label>
        <select
          v-model="form.category_id"
          required
          class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary focus:outline-none focus:border-blue-500 font-mono"
        >
          <option value="" disabled>Select an IT Category</option>
          <option
            v-for="cat in authStore.meta?.categories || []"
            :key="cat.id"
            :value="cat.id"
          >
            {{ cat.name }} &bull; {{ cat.description }}
          </option>
        </select>
      </div>

      <!-- Severity / Priority Selector with SLA Target Hints -->
      <div>
        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-2">
          Severity Level &amp; Impact <span class="text-red-600">*</span>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
          <label
            v-for="p in priorityOptions"
            :key="p.value"
            class="flex items-start p-3 rounded border cursor-pointer transition-subtle"
            :class="form.priority === p.value ? 'bg-blue-50/80 border-blue-500 ring-1 ring-blue-500' : 'bg-bg-surface border-border-default hover:border-border-strong'"
          >
            <input
              v-model="form.priority"
              type="radio"
              :value="p.value"
              class="mt-0.5 text-blue-600 focus:ring-0 mr-2.5"
            />
            <div class="text-xs">
              <div class="font-bold text-text-primary flex items-center space-x-1.5">
                <span>{{ p.label }}</span>
                <span class="text-[10px] font-mono px-1 rounded bg-bg-surface-raised border border-border-default text-text-secondary">
                  {{ p.slaHint }}
                </span>
              </div>
              <div class="text-[11px] text-text-muted mt-0.5 leading-snug">{{ p.desc }}</div>
            </div>
          </label>
        </div>
      </div>

      <!-- Detailed Description -->
      <div>
        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">
          Detailed Symptoms &amp; Diagnostic Context <span class="text-red-600">*</span>
        </label>
        <textarea
          v-model="form.description"
          required
          rows="5"
          placeholder="Include exact error codes, steps to reproduce, affected workstation hostname, or recent changes..."
          class="w-full p-3 bg-bg-base border border-border-default rounded text-xs text-text-primary placeholder:text-text-muted focus:outline-none focus:border-blue-500 font-sans"
        />
      </div>

      <!-- File Attachment Simulated Box -->
      <div>
        <label class="block text-xs font-semibold text-text-secondary uppercase tracking-wider mb-1.5">
          Attachments / Logs / Screenshots
        </label>
        <div class="border-2 border-dashed border-border-default rounded-lg p-6 text-center hover:border-border-strong transition-subtle">
          <UploadCloud class="w-8 h-8 text-text-muted mx-auto mb-2" />
          <div class="text-xs font-medium text-text-primary">Drag &amp; drop diagnostic screenshots or error logs</div>
          <div class="text-[10px] text-text-muted font-mono mt-1">PNG, JPG, PDF, LOG up to 10MB</div>
        </div>
      </div>

      <!-- Form Action Buttons -->
      <div class="pt-4 border-t border-border-default flex items-center justify-end space-x-3">
        <RouterLink
          to="/tickets"
          class="px-4 py-2 rounded text-xs font-medium text-text-secondary hover:text-text-primary hover:bg-bg-surface-raised transition-subtle"
        >
          Cancel
        </RouterLink>
        <button
          type="submit"
          :disabled="ticketStore.isActionLoading"
          class="px-5 py-2 rounded bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-sm transition-subtle disabled:opacity-50 flex items-center space-x-2"
        >
          <span v-if="ticketStore.isActionLoading">Creating Incident...</span>
          <span v-else>Submit Incident</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useTicketStore } from '@/stores/ticket'
import { useAuthStore } from '@/stores/auth'
import { ArrowLeft, UploadCloud } from 'lucide-vue-next'

const router = useRouter()
const ticketStore = useTicketStore()
const authStore = useAuthStore()

const errorMessage = ref('')

const form = reactive({
  title: '',
  description: '',
  category_id: '' as any,
  priority: 'medium',
})

const priorityOptions = [
  {
    value: 'low',
    label: 'Low Severity',
    slaHint: '48h target',
    desc: 'General inquiry, minor hardware request, cosmetic flaw.',
  },
  {
    value: 'medium',
    label: 'Standard Priority',
    slaHint: '24h target',
    desc: 'Individual user degraded workflow; workarounds exist.',
  },
  {
    value: 'high',
    label: 'High Urgency',
    slaHint: '8h target',
    desc: 'Critical business function impaired for multiple users.',
  },
  {
    value: 'critical',
    label: 'Critical Outage',
    slaHint: '2h target',
    desc: 'Complete service failure, security breach, company-wide block.',
  },
]

async function handleSubmit() {
  errorMessage.value = ''
  try {
    const created = await ticketStore.createTicket({
      title: form.title,
      description: form.description,
      category_id: parseInt(form.category_id, 10),
      priority: form.priority,
    })
    router.push(`/tickets/${created.id}`)
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to submit incident ticket.'
  }
}

onMounted(async () => {
  await authStore.fetchMeta()
  if (authStore.meta?.categories?.length) {
    form.category_id = authStore.meta.categories[0].id
  }
})
</script>

