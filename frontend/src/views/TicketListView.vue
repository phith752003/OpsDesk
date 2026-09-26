<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold tracking-tight text-text-primary">
          Incident &amp; Request Queue
        </h1>
        <p class="text-xs text-text-muted mt-0.5 font-mono">
          {{ ticketStore.pagination.total }} total records in system
        </p>
      </div>
      <RouterLink
        to="/tickets/new"
        class="inline-flex items-center px-3.5 py-2 rounded bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-sm transition-subtle space-x-1.5 self-start sm:self-auto"
      >
        <PlusCircle class="w-4 h-4" />
        <span>Create Incident Request</span>
      </RouterLink>
    </div>

    <!-- Multi-Filter Toolbar -->
    <div class="p-4 bg-bg-surface border border-border-default rounded-lg space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Search Query -->
        <div class="lg:col-span-2">
          <div class="relative">
            <Search class="w-4 h-4 absolute left-3 top-2.5 text-text-muted" />
            <input
              v-model="ticketStore.filters.search"
              type="text"
              placeholder="Search by code, title, or keyword..."
              class="w-full pl-9 pr-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary placeholder:text-text-muted focus:outline-none focus:border-blue-500 font-mono"
              @input="handleFilterChange"
            />
          </div>
        </div>

        <!-- Status Filter -->
        <div>
          <select
            v-model="ticketStore.filters.status"
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary focus:outline-none focus:border-blue-500 font-mono"
            @change="handleFilterChange"
          >
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="assigned">Assigned</option>
            <option value="in_progress">In Progress</option>
            <option value="pending">Pending Customer</option>
            <option value="resolved">Resolved</option>
            <option value="closed">Closed</option>
          </select>
        </div>

        <!-- Priority Filter -->
        <div>
          <select
            v-model="ticketStore.filters.priority"
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary focus:outline-none focus:border-blue-500 font-mono"
            @change="handleFilterChange"
          >
            <option value="">All Priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>

        <!-- Category Filter -->
        <div>
          <select
            v-model="ticketStore.filters.category_id"
            class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary focus:outline-none focus:border-blue-500 font-mono"
            @change="handleFilterChange"
          >
            <option value="">All Categories</option>
            <option
              v-for="cat in authStore.meta?.categories || []"
              :key="cat.id"
              :value="cat.id"
            >
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Quick Reset Button -->
      <div v-if="hasActiveFilters" class="flex items-center justify-between pt-2 border-t border-border-subtle text-xs">
        <span class="text-text-muted">Filtering active</span>
        <button
          type="button"
          class="text-blue-400 hover:text-blue-300 font-mono underline"
          @click="resetFilters"
        >
          Reset Filters
        </button>
      </div>
    </div>

    <!-- Data Table Container -->
    <div class="bg-bg-surface border border-border-default rounded-lg overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-bg-surface-raised border-b border-border-subtle text-text-muted font-mono uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3.5">Code</th>
              <th class="px-4 py-3.5">Incident Title</th>
              <th class="px-4 py-3.5">Severity</th>
              <th class="px-4 py-3.5">Status</th>
              <th class="px-4 py-3.5">Requester</th>
              <th class="px-4 py-3.5">Assignee</th>
              <th class="px-4 py-3.5">SLA Deadline</th>
              <th class="px-4 py-3.5 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle">
            <!-- Loading Indicator -->
            <tr v-if="ticketStore.isLoading">
              <td colspan="8" class="px-4 py-12 text-center text-text-muted font-mono">
                <div class="inline-flex items-center space-x-2">
                  <span class="w-3 h-3 rounded-full border-2 border-blue-500 border-t-transparent animate-spin" />
                  <span>Loading incident records...</span>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="ticketStore.tickets.length === 0">
              <td colspan="8" class="px-4 py-12 text-center text-text-muted">
                <TicketIcon class="w-8 h-8 mx-auto mb-2 text-text-muted/60" />
                <div class="text-sm font-semibold text-text-primary">No matching incidents found</div>
                <p class="text-xs text-text-muted mt-1">Adjust search parameters or create a new incident request.</p>
              </td>
            </tr>

            <!-- Data Rows -->
            <tr
              v-for="t in ticketStore.tickets"
              :key="t.id"
              class="hover:bg-bg-surface-hover/50 transition-subtle group"
            >
              <!-- Ticket Monospace Reference Badge -->
              <td class="px-4 py-3 font-mono whitespace-nowrap">
                <RouterLink
                  :to="`/tickets/${t.id}`"
                  class="font-semibold text-blue-400 hover:text-blue-300 group-hover:underline"
                >
                  {{ t.reference_no }}
                </RouterLink>
              </td>

              <!-- Title & Category -->
              <td class="px-4 py-3 max-w-sm">
                <RouterLink
                  :to="`/tickets/${t.id}`"
                  class="font-medium text-text-primary hover:text-blue-300 block truncate"
                >
                  {{ t.title }}
                </RouterLink>
                <span class="text-[11px] text-text-muted block mt-0.5">
                  {{ t.category?.name }}
                </span>
              </td>

              <!-- Priority -->
              <td class="px-4 py-3 whitespace-nowrap">
                <PriorityBadge :priority="t.priority" />
              </td>

              <!-- Status -->
              <td class="px-4 py-3 whitespace-nowrap">
                <StatusBadge :status="t.status" />
              </td>

              <!-- Requester -->
              <td class="px-4 py-3 whitespace-nowrap text-text-secondary">
                <div class="font-medium text-text-primary">{{ t.requester?.name }}</div>
                <div class="text-[10px] text-text-muted font-mono">{{ t.requester?.email }}</div>
              </td>

              <!-- Assignee -->
              <td class="px-4 py-3 whitespace-nowrap">
                <span
                  v-if="t.agent"
                  class="text-xs text-text-primary font-medium"
                >
                  {{ t.agent.name }}
                </span>
                <span
                  v-else
                  class="inline-block px-2 py-0.5 rounded bg-gray-800 text-gray-400 text-[11px] font-mono"
                >
                  Unassigned
                </span>
              </td>

              <!-- SLA Countdown -->
              <td class="px-4 py-3 whitespace-nowrap">
                <SlaCountdown
                  :resolution-due-at="t.resolution_due_at"
                  :resolved-at="t.resolved_at"
                  :is-breached="t.is_sla_breached"
                  :is-at-risk="t.is_at_risk"
                />
              </td>

              <!-- Actions -->
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <RouterLink
                  :to="`/tickets/${t.id}`"
                  class="px-2.5 py-1 rounded bg-bg-surface-raised border border-border-subtle hover:border-blue-500/50 text-blue-400 text-xs font-mono transition-subtle"
                >
                  Details &rarr;
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="px-4 py-3 bg-bg-surface-raised/40 border-t border-border-default flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-text-muted font-mono">
        <div>
          Showing page <span class="text-text-primary font-semibold">{{ ticketStore.pagination.currentPage }}</span> of <span class="text-text-primary font-semibold">{{ ticketStore.pagination.lastPage }}</span>
          (<span class="text-text-primary">{{ ticketStore.pagination.total }}</span> tickets)
        </div>
        <div class="flex items-center space-x-2">
          <button
            type="button"
            :disabled="ticketStore.pagination.currentPage <= 1 || ticketStore.isLoading"
            class="px-3 py-1 rounded bg-bg-surface border border-border-default text-text-secondary hover:text-text-primary hover:bg-bg-surface-hover disabled:opacity-40 transition-subtle"
            @click="goToPage(ticketStore.pagination.currentPage - 1)"
          >
            &larr; Previous
          </button>
          <button
            type="button"
            :disabled="ticketStore.pagination.currentPage >= ticketStore.pagination.lastPage || ticketStore.isLoading"
            class="px-3 py-1 rounded bg-bg-surface border border-border-default text-text-secondary hover:text-text-primary hover:bg-bg-surface-hover disabled:opacity-40 transition-subtle"
            @click="goToPage(ticketStore.pagination.currentPage + 1)"
          >
            Next &rarr;
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useTicketStore } from '@/stores/ticket'
import { useAuthStore } from '@/stores/auth'
import StatusBadge from '@/components/common/StatusBadge.vue'
import PriorityBadge from '@/components/common/PriorityBadge.vue'
import SlaCountdown from '@/components/common/SlaCountdown.vue'
import { PlusCircle, Search, Ticket as TicketIcon } from 'lucide-vue-next'

const ticketStore = useTicketStore()
const authStore = useAuthStore()

let debounceTimeout: any = null

const hasActiveFilters = computed(() => {
  const f = ticketStore.filters
  return !!(f.status || f.priority || f.category_id || f.agent_id || f.search)
})

function handleFilterChange() {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    ticketStore.fetchTickets(1)
  }, 300)
}

function resetFilters() {
  ticketStore.filters = {
    status: '',
    priority: '',
    category_id: '',
    agent_id: '',
    search: '',
  }
  ticketStore.fetchTickets(1)
}

function goToPage(page: number) {
  ticketStore.fetchTickets(page)
}

onMounted(async () => {
  await Promise.all([
    ticketStore.fetchTickets(1),
    authStore.fetchMeta(),
  ])
})
</script>
