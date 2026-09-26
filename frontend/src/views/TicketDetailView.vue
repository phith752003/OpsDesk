<template>
  <div v-if="ticketStore.isLoading" class="py-16 text-center text-text-muted font-mono">
    <div class="inline-flex items-center space-x-2">
      <span class="w-4 h-4 rounded-full border-2 border-blue-500 border-t-transparent animate-spin" />
      <span>Loading incident command view...</span>
    </div>
  </div>

  <div v-else-if="ticket" class="space-y-6">
    <!-- Top Action & Navigation Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-border-default">
      <div class="flex items-center space-x-3">
        <RouterLink
          to="/tickets"
          class="p-1.5 rounded bg-bg-surface-raised border border-border-default hover:text-white transition-subtle"
        >
          <ArrowLeft class="w-4 h-4" />
        </RouterLink>
        <div class="flex items-center space-x-2 font-mono">
          <span class="text-base font-bold text-blue-400 bg-blue-950/60 px-2.5 py-1 rounded border border-blue-500/40">
            {{ ticket.reference_no }}
          </span>
          <PriorityBadge :priority="ticket.priority" />
          <StatusBadge :status="ticket.status" />
        </div>
      </div>

      <div class="flex items-center space-x-3">
        <SlaCountdown
          :resolution-due-at="ticket.resolution_due_at"
          :resolved-at="ticket.resolved_at"
          :is-breached="ticket.is_sla_breached"
          :is-at-risk="ticket.is_at_risk"
        />
      </div>
    </div>

    <!-- 2-Column Split: Context & Feed (Left 8 cols) vs Actions & Metadata (Right 4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left Column: Problem Context & Conversation Feed -->
      <div class="lg:col-span-8 space-y-6">
        <!-- Ticket Primary Details Card -->
        <div class="bg-bg-surface border border-border-default rounded-lg p-6">
          <h1 class="text-lg font-bold text-text-primary tracking-tight mb-2">
            {{ ticket.title }}
          </h1>
          <div class="flex flex-wrap items-center gap-3 text-xs text-text-muted font-mono mb-4 pb-4 border-b border-border-subtle">
            <span>Category: <strong class="text-text-secondary">{{ ticket.category?.name }}</strong></span>
            <span>&bull;</span>
            <span>Submitted by: <strong class="text-text-secondary">{{ ticket.requester?.name }}</strong></span>
            <span>&bull;</span>
            <span>{{ formatDate(ticket.created_at) }}</span>
          </div>
          <div class="text-sm text-text-primary whitespace-pre-line leading-relaxed">
            {{ ticket.description }}
          </div>
        </div>

        <!-- Add Reply / Internal Note Form -->
        <div class="bg-bg-surface border border-border-default rounded-lg overflow-hidden shadow-sm">
          <!-- Mode Tabs -->
          <div class="flex border-b border-border-default bg-bg-surface-raised/40">
            <button
              type="button"
              class="px-4 py-2.5 text-xs font-semibold flex items-center space-x-2 transition-subtle border-r border-border-default"
              :class="!isInternalNoteMode ? 'bg-bg-surface text-blue-400 border-b-2 border-b-blue-500' : 'text-text-secondary hover:text-text-primary'"
              @click="isInternalNoteMode = false"
            >
              <MessageSquare class="w-3.5 h-3.5" />
              <span>Public Response to Requester</span>
            </button>

            <button
              v-if="authStore.isStaff"
              type="button"
              class="px-4 py-2.5 text-xs font-semibold flex items-center space-x-2 transition-subtle"
              :class="isInternalNoteMode ? 'bg-amber-950/40 text-amber-300 border-b-2 border-b-amber-500' : 'text-text-secondary hover:text-amber-300'"
              @click="isInternalNoteMode = true"
            >
              <Lock class="w-3.5 h-3.5 text-amber-400" />
              <span>Confidential Internal Note</span>
            </button>
          </div>

          <!-- Note Form Content -->
          <div class="p-4 space-y-3" :class="isInternalNoteMode ? 'bg-amber-950/10' : ''">
            <div v-if="isInternalNoteMode" class="text-[11px] text-amber-400/90 font-mono flex items-center space-x-1.5">
              <Lock class="w-3 h-3" />
              <span>Private engineering log — visible strictly to IT support staff &amp; administrators.</span>
            </div>

            <textarea
              v-model="newCommentBody"
              rows="3"
              class="w-full p-3 bg-bg-base border border-border-default rounded text-xs text-text-primary placeholder:text-text-muted focus:outline-none focus:border-blue-500 font-sans"
              :placeholder="isInternalNoteMode ? 'Record technical diagnostics, hardware serials, or internal handover details...' : 'Write an update visible to the ticket requester...'"
            />

            <div class="flex items-center justify-between">
              <span class="text-[10px] text-text-muted font-mono">Supports multi-line text &amp; diagnostic logs</span>
              <button
                type="button"
                :disabled="!newCommentBody.trim() || ticketStore.isActionLoading"
                class="px-4 py-2 rounded text-xs font-semibold text-white transition-subtle disabled:opacity-40"
                :class="isInternalNoteMode ? 'bg-amber-600 hover:bg-amber-500' : 'bg-blue-600 hover:bg-blue-500'"
                @click="submitComment"
              >
                <span v-if="ticketStore.isActionLoading">Submitting...</span>
                <span v-else>{{ isInternalNoteMode ? 'Record Internal Note' : 'Post Public Reply' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Combined Discussion & Audit Timeline -->
        <div class="space-y-4">
          <h2 class="text-xs font-semibold text-text-secondary uppercase tracking-wider font-mono">
            Chronological Activity &amp; Discussion Feed
          </h2>

          <div v-if="feedItems.length === 0" class="p-6 bg-bg-surface border border-border-default rounded-lg text-center text-xs text-text-muted">
            No replies or audit events recorded yet.
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="item in feedItems"
              :key="item.feedId"
              class="rounded-lg border p-4 transition-subtle"
              :class="item.isInternal ? 'bg-amber-950/15 border-amber-500/30' : item.isAudit ? 'bg-bg-base border-border-subtle py-2.5 px-4 text-xs' : 'bg-bg-surface border-border-default'"
            >
              <!-- Audit Trail Entry -->
              <div v-if="item.isAudit" class="flex items-center justify-between text-text-muted font-mono text-[11px]">
                <div class="flex items-center space-x-2">
                  <Activity class="w-3.5 h-3.5 text-blue-400" />
                  <span>
                    <strong class="text-text-secondary">{{ item.actorName }}</strong>
                    {{ formatAuditAction(item.action, item.metadata) }}
                  </span>
                </div>
                <span>{{ formatDate(item.created_at) }}</span>
              </div>

              <!-- Discussion Comment / Internal Note Entry -->
              <div v-else>
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center space-x-2.5">
                    <div
                      class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold"
                      :class="item.isInternal ? 'bg-amber-900 text-amber-200' : 'bg-blue-900 text-blue-200'"
                    >
                      {{ item.userName?.charAt(0) || 'U' }}
                    </div>
                    <span class="text-xs font-semibold text-text-primary">{{ item.userName }}</span>
                    <span
                      v-if="item.isInternal"
                      class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-amber-900/60 text-amber-300 border border-amber-500/30"
                    >
                      <Lock class="w-2.5 h-2.5 mr-1" />
                      INTERNAL NOTE
                    </span>
                    <span v-else class="text-[10px] text-text-muted font-mono capitalize">
                      {{ item.userRole }}
                    </span>
                  </div>
                  <span class="text-[11px] text-text-muted font-mono">{{ formatDate(item.created_at) }}</span>
                </div>
                <div class="text-xs text-text-primary whitespace-pre-line pl-8.5">
                  {{ item.body }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Operational Controls & Metadata -->
      <div class="lg:col-span-4 space-y-6">
        <!-- Operational Action Dock -->
        <div class="bg-bg-surface border border-border-default rounded-lg p-5 space-y-4">
          <h2 class="text-xs font-semibold text-text-primary uppercase tracking-wider font-mono border-b border-border-subtle pb-2">
            Incident Control Dock
          </h2>

          <!-- Status Selector -->
          <div>
            <label class="block text-[11px] font-mono text-text-muted uppercase mb-1">
              Workflow Status
            </label>
            <select
              :value="ticket.status"
              :disabled="ticketStore.isActionLoading"
              class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary focus:outline-none focus:border-blue-500 font-mono"
              @change="handleStatusChange(($event.target as HTMLSelectElement).value)"
            >
              <option value="open">Open</option>
              <option value="assigned">Assigned</option>
              <option value="in_progress">In Progress</option>
              <option value="pending">Pending Customer</option>
              <option value="resolved">Resolved</option>
              <option value="closed">Closed</option>
            </select>
          </div>

          <!-- Assignee Selector (Staff Only) -->
          <div v-if="authStore.isStaff">
            <label class="block text-[11px] font-mono text-text-muted uppercase mb-1">
              Assigned Specialist
            </label>
            <select
              :value="ticket.agent?.id ?? ''"
              :disabled="ticketStore.isActionLoading"
              class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary focus:outline-none focus:border-blue-500 font-mono"
              @change="handleAgentChange(($event.target as HTMLSelectElement).value)"
            >
              <option value="">Unassigned</option>
              <option
                v-for="agent in agentList"
                :key="agent.id"
                :value="agent.id"
              >
                {{ agent.name }} ({{ agent.department?.code || 'IT' }})
              </option>
            </select>
          </div>

          <!-- Priority Escalation (Staff Only) -->
          <div v-if="authStore.isStaff">
            <label class="block text-[11px] font-mono text-text-muted uppercase mb-1">
              Severity Level
            </label>
            <select
              :value="ticket.priority"
              :disabled="ticketStore.isActionLoading"
              class="w-full px-3 py-2 bg-bg-base border border-border-default rounded text-xs text-text-primary focus:outline-none focus:border-blue-500 font-mono"
              @change="handlePriorityChange(($event.target as HTMLSelectElement).value)"
            >
              <option value="low">Low (Standard Request)</option>
              <option value="medium">Medium (Non-blocking)</option>
              <option value="high">High (Urgent Incident)</option>
              <option value="critical">Critical (Service Outage)</option>
            </select>
          </div>
        </div>

        <!-- SLA Deadlines Card -->
        <div class="bg-bg-surface border border-border-default rounded-lg p-5 space-y-3">
          <h2 class="text-xs font-semibold text-text-primary uppercase tracking-wider font-mono border-b border-border-subtle pb-2 flex items-center justify-between">
            <span>SLA Performance Targets</span>
            <ShieldAlert class="w-3.5 h-3.5 text-blue-400" />
          </h2>

          <div class="space-y-2 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-text-muted font-mono">Response Target:</span>
              <span class="text-text-primary font-mono tabular-nums">
                {{ ticket.response_due_at ? formatDate(ticket.response_due_at) : '—' }}
              </span>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-text-muted font-mono">Initial Response:</span>
              <span class="text-text-primary font-mono tabular-nums">
                {{ ticket.responded_at ? formatDate(ticket.responded_at) : 'Awaiting Response' }}
              </span>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-text-muted font-mono">Resolution Deadline:</span>
              <span class="text-text-primary font-mono tabular-nums">
                {{ ticket.resolution_due_at ? formatDate(ticket.resolution_due_at) : '—' }}
              </span>
            </div>

            <div v-if="ticket.resolved_at" class="flex items-center justify-between text-emerald-400">
              <span class="font-mono">Resolved At:</span>
              <span class="font-mono tabular-nums">{{ formatDate(ticket.resolved_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Requester Information Card -->
        <div class="bg-bg-surface border border-border-default rounded-lg p-5 space-y-3">
          <h2 class="text-xs font-semibold text-text-primary uppercase tracking-wider font-mono border-b border-border-subtle pb-2">
            Requester Profile
          </h2>
          <div class="space-y-1.5 text-xs">
            <div class="text-text-primary font-semibold">{{ ticket.requester?.name }}</div>
            <div class="text-text-muted font-mono text-[11px]">{{ ticket.requester?.email }}</div>
            <div class="text-text-secondary pt-1">
              Department: <span class="text-text-primary">{{ ticket.requester?.department?.name || 'Operations' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useTicketStore } from '@/stores/ticket'
import { useAuthStore } from '@/stores/auth'
import { userApi } from '@/services/userApi'
import PriorityBadge from '@/components/common/PriorityBadge.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import SlaCountdown from '@/components/common/SlaCountdown.vue'
import type { User } from '@/types'
import {
  ArrowLeft,
  MessageSquare,
  Lock,
  Activity,
  ShieldAlert,
} from 'lucide-vue-next'

const route = useRoute()
const ticketStore = useTicketStore()
const authStore = useAuthStore()

const newCommentBody = ref('')
const isInternalNoteMode = ref(false)
const agentList = ref<User[]>([])

const ticket = computed(() => ticketStore.activeTicket)

// Interleave comments and audit logs chronologically
const feedItems = computed(() => {
  if (!ticket.value) return []

  const items: any[] = []

  // Add comments
  if (ticket.value.comments) {
    ticket.value.comments.forEach((c) => {
      items.push({
        feedId: `comment-${c.id}`,
        timestamp: new Date(c.created_at).getTime(),
        created_at: c.created_at,
        isAudit: false,
        isInternal: c.is_internal,
        userName: c.user.name,
        userRole: c.user.role,
        body: c.body,
      })
    })
  }

  // Add activity logs
  if (ticket.value.activity_logs) {
    ticket.value.activity_logs.forEach((a) => {
      items.push({
        feedId: `audit-${a.id}`,
        timestamp: new Date(a.created_at).getTime(),
        created_at: a.created_at,
        isAudit: true,
        actorName: a.user?.name || 'System Engine',
        action: a.action,
        metadata: a.metadata,
      })
    })
  }

  return items.sort((a, b) => a.timestamp - b.timestamp)
})

function formatDate(isoString: string) {
  if (!isoString) return '—'
  const d = new Date(isoString)
  return d.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatAuditAction(action: string, metadata?: any) {
  switch (action) {
    case 'created':
      return 'submitted this incident.'
    case 'assigned':
      return `assigned this ticket to ${metadata?.to || 'specialist'}.`
    case 'status_changed':
      return `transitioned status to ${metadata?.to?.replace('_', ' ')}.`
    case 'priority_changed':
      return `escalated priority to ${metadata?.to}.`
    case 'resolved':
      return 'marked this incident as resolved.'
    case 'closed':
      return 'closed this incident record.'
    default:
      return `performed ${action.replace('_', ' ')}.`
  }
}

async function handleStatusChange(newStatus: string) {
  if (!ticket.value) return
  await ticketStore.updateStatus(ticket.value.id, newStatus)
}

async function handleAgentChange(agentIdString: string) {
  if (!ticket.value) return
  const agentId = agentIdString ? parseInt(agentIdString, 10) : null
  await ticketStore.assignTicket(ticket.value.id, agentId)
}

async function handlePriorityChange(newPriority: string) {
  if (!ticket.value) return
  await ticketStore.updatePriority(ticket.value.id, newPriority)
}

async function submitComment() {
  if (!ticket.value || !newCommentBody.value.trim()) return
  await ticketStore.addComment(ticket.value.id, newCommentBody.value, isInternalNoteMode.value)
  newCommentBody.value = ''
}

onMounted(async () => {
  const ticketId = route.params.id as string
  await ticketStore.fetchTicket(ticketId)

  if (authStore.isStaff) {
    try {
      const res = await userApi.getAgents()
      agentList.value = res.agents
    } catch (e) {
      console.error('Failed to load agents', e)
    }
  }
})
</script>
