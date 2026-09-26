import { defineStore } from 'pinia'
import { ref } from 'vue'
import { ticketApi, type TicketFilterParams } from '@/services/ticketApi'
import type { Ticket } from '@/types'

export const useTicketStore = defineStore('tickets', () => {
  const tickets = ref<Ticket[]>([])
  const activeTicket = ref<Ticket | null>(null)
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 15,
  })
  const filters = ref<TicketFilterParams>({
    status: '',
    priority: '',
    category_id: '',
    agent_id: '',
    search: '',
  })
  const isLoading = ref(false)
  const isActionLoading = ref(false)

  async function fetchTickets(page: number = 1) {
    isLoading.value = true
    try {
      const params = {
        ...filters.value,
        page,
        per_page: pagination.value.perPage,
      }
      // Remove empty filters
      Object.keys(params).forEach((key) => {
        if ((params as any)[key] === '' || (params as any)[key] === null) {
          delete (params as any)[key]
        }
      })

      const res = await ticketApi.getTickets(params)
      tickets.value = res.data
      if (res.meta) {
        pagination.value = {
          currentPage: res.meta.current_page,
          lastPage: res.meta.last_page,
          total: res.meta.total,
          perPage: res.meta.per_page,
        }
      }
    } finally {
      isLoading.value = false
    }
  }

  async function fetchTicket(id: number | string) {
    isLoading.value = true
    try {
      const data = await ticketApi.getTicket(id)
      activeTicket.value = data
      return data
    } finally {
      isLoading.value = false
    }
  }

  async function createTicket(payload: {
    title: string
    description: string
    category_id: number
    priority: string
    agent_id?: number | null
  }) {
    isActionLoading.value = true
    try {
      const res = await ticketApi.createTicket(payload)
      return res.ticket
    } finally {
      isActionLoading.value = false
    }
  }

  async function updateStatus(ticketId: number, status: string) {
    isActionLoading.value = true
    try {
      const res = await ticketApi.updateStatus(ticketId, status)
      if (activeTicket.value && activeTicket.value.id === ticketId) {
        activeTicket.value.status = res.ticket.status
        activeTicket.value.status_label = res.ticket.status_label
        activeTicket.value.resolved_at = res.ticket.resolved_at
        activeTicket.value.closed_at = res.ticket.closed_at
      }
      // Update in ticket list if present
      const index = tickets.value.findIndex((t) => t.id === ticketId)
      if (index !== -1) {
        tickets.value[index] = { ...tickets.value[index], ...res.ticket }
      }
      return res.ticket
    } finally {
      isActionLoading.value = false
    }
  }

  async function assignTicket(ticketId: number, agentId: number | null) {
    isActionLoading.value = true
    try {
      const res = await ticketApi.assignTicket(ticketId, agentId)
      if (activeTicket.value && activeTicket.value.id === ticketId) {
        activeTicket.value.agent = res.ticket.agent
        activeTicket.value.status = res.ticket.status
        activeTicket.value.status_label = res.ticket.status_label
      }
      const index = tickets.value.findIndex((t) => t.id === ticketId)
      if (index !== -1) {
        tickets.value[index] = { ...tickets.value[index], ...res.ticket }
      }
      return res.ticket
    } finally {
      isActionLoading.value = false
    }
  }

  async function updatePriority(ticketId: number, priority: string) {
    isActionLoading.value = true
    try {
      const res = await ticketApi.updatePriority(ticketId, priority)
      if (activeTicket.value && activeTicket.value.id === ticketId) {
        activeTicket.value.priority = res.ticket.priority
        activeTicket.value.priority_label = res.ticket.priority_label
        activeTicket.value.response_due_at = res.ticket.response_due_at
        activeTicket.value.resolution_due_at = res.ticket.resolution_due_at
      }
      return res.ticket
    } finally {
      isActionLoading.value = false
    }
  }

  async function addComment(ticketId: number, body: string, isInternal: boolean = false) {
    isActionLoading.value = true
    try {
      const res = await ticketApi.addComment(ticketId, body, isInternal)
      if (activeTicket.value && activeTicket.value.id === ticketId) {
        if (!activeTicket.value.comments) {
          activeTicket.value.comments = []
        }
        activeTicket.value.comments.push(res.comment)
      }
      return res.comment
    } finally {
      isActionLoading.value = false
    }
  }

  return {
    tickets,
    activeTicket,
    pagination,
    filters,
    isLoading,
    isActionLoading,
    fetchTickets,
    fetchTicket,
    createTicket,
    updateStatus,
    assignTicket,
    updatePriority,
    addComment,
  }
})
