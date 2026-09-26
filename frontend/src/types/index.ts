export type UserRole = 'admin' | 'agent' | 'requester' | 'manager'

export type TicketPriority = 'low' | 'medium' | 'high' | 'critical'

export type TicketStatus = 'open' | 'assigned' | 'in_progress' | 'pending' | 'resolved' | 'closed'

export interface Department {
  id: number
  name: string
  code: string
  description?: string
}

export interface User {
  id: number
  name: string
  email: string
  role: UserRole
  role_label?: string
  department?: Department
  is_active: boolean
  created_at: string
}

export interface Category {
  id: number
  name: string
  slug: string
  description?: string
  is_active: boolean
}

export interface SlaPolicy {
  id: number
  name: string
  priority: TicketPriority
  response_time_minutes: number
  resolution_time_minutes: number
  is_default: boolean
}

export interface Comment {
  id: number
  ticket_id: number
  body: string
  is_internal: boolean
  user: {
    id: number
    name: string
    email: string
    role: UserRole
  }
  created_at: string
}

export interface Attachment {
  id: number
  file_name: string
  file_path: string
  file_type: string
  file_size: number
  created_at: string
}

export interface ActivityLog {
  id: number
  ticket_id: number
  action: string
  metadata?: Record<string, any>
  user?: {
    id: number
    name: string
    role: UserRole
  }
  created_at: string
}

export interface Ticket {
  id: number
  reference_no: string
  title: string
  description: string
  priority: TicketPriority
  priority_label?: string
  status: TicketStatus
  status_label?: string
  category?: Category
  requester?: User
  agent?: User | null
  sla_policy?: SlaPolicy | null
  response_due_at?: string | null
  responded_at?: string | null
  resolution_due_at?: string | null
  resolved_at?: string | null
  closed_at?: string | null
  is_sla_breached: boolean
  is_at_risk: boolean
  resolution_remaining_minutes?: number | null
  comments?: Comment[]
  attachments?: Attachment[]
  activity_logs?: ActivityLog[]
  created_at: string
  updated_at: string
}

export interface DashboardMetrics {
  total_tickets: number
  open_tickets: number
  unassigned_tickets: number
  in_progress_tickets: number
  breached_tickets: number
  sla_compliance_percentage: number
  avg_response_time_minutes: number
  avg_resolution_time_minutes: number
  tickets_by_priority: Record<TicketPriority, number>
  tickets_by_status: Record<TicketStatus, number>
}

export interface AgentWorkload {
  agent_id: number
  name: string
  email: string
  department: string
  active_tickets_count: number
  in_progress_count: number
  resolved_today_count: number
  breached_count: number
  capacity_percentage: number
}

export interface ReportsTrends {
  category_breakdown: Array<{ name: string; count: number }>
  volume_trend: Array<{ date: string; created: number; resolved: number }>
  sla_by_priority: Array<{ priority: string; compliance_rate: number; total_resolved: number }>
}
