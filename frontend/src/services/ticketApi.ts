import api from './api'
import type { Ticket, Comment } from '@/types'

export interface TicketFilterParams {
  status?: string
  priority?: string
  category_id?: number | string
  agent_id?: number | string
  search?: string
  page?: number
  per_page?: number
}

export interface PaginatedTickets {
  data: Ticket[]
  meta?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export const ticketApi = {
  async getTickets(params: TicketFilterParams = {}): Promise<PaginatedTickets> {
    const res = await api.get('/tickets', { params })
    return {
      data: res.data.data || res.data,
      meta: res.data.meta,
    }
  },

  async getTicket(id: number | string): Promise<Ticket> {
    const res = await api.get<Ticket>(`/tickets/${id}`)
    return res.data
  },

  async createTicket(payload: {
    title: string
    description: string
    category_id: number
    priority: string
    agent_id?: number | null
  }): Promise<{ ticket: Ticket; message: string }> {
    const res = await api.post('/tickets', payload)
    return res.data
  },

  async updateStatus(id: number | string, status: string): Promise<{ ticket: Ticket; message: string }> {
    const res = await api.patch(`/tickets/${id}/status`, { status })
    return res.data
  },

  async assignTicket(id: number | string, agentId: number | null): Promise<{ ticket: Ticket; message: string }> {
    const res = await api.patch(`/tickets/${id}/assign`, { agent_id: agentId })
    return res.data
  },

  async updatePriority(id: number | string, priority: string): Promise<{ ticket: Ticket; message: string }> {
    const res = await api.patch(`/tickets/${id}/priority`, { priority })
    return res.data
  },

  async addComment(
    ticketId: number | string,
    body: string,
    isInternal: boolean = false
  ): Promise<{ comment: Comment; message: string }> {
    const res = await api.post(`/tickets/${ticketId}/comments`, {
      body,
      is_internal: isInternal,
    })
    return res.data
  },
}
