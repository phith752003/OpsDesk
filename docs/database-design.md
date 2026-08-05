# OpsDesk — Database Design & Data Modeling

## 1. Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    DEPARTMENTS ||--o{ USERS : "has members"
    USERS ||--o{ TICKETS : "requests (requester_id)"
    USERS ||--o{ TICKETS : "resolves (agent_id)"
    USERS ||--o{ COMMENTS : "authors"
    USERS ||--o{ ACTIVITY_LOGS : "triggers"
    CATEGORIES ||--o{ TICKETS : "categorizes"
    SLA_POLICIES ||--o{ TICKETS : "binds to"
    TICKETS ||--o{ COMMENTS : "contains discussion"
    TICKETS ||--o{ ATTACHMENTS : "includes files"
    TICKETS ||--o{ ACTIVITY_LOGS : "logs history"

    DEPARTMENTS {
        bigint id PK
        string name
        string code UK
        string description
        timestamps
    }

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "admin, agent, requester, manager"
        bigint department_id FK
        boolean is_active
        timestamps
    }

    CATEGORIES {
        bigint id PK
        string name
        string slug UK
        string description
        timestamps
    }

    SLA_POLICIES {
        bigint id PK
        string name
        enum priority "low, medium, high, critical" UK
        integer response_time_minutes
        integer resolution_time_minutes
        boolean is_default
        timestamps
    }

    TICKETS {
        bigint id PK
        string reference_no UK
        string title
        text description
        enum priority "low, medium, high, critical"
        enum status "open, assigned, in_progress, pending, resolved, closed"
        bigint category_id FK
        bigint requester_id FK
        bigint agent_id FK "nullable"
        bigint sla_policy_id FK
        timestamp response_due_at
        timestamp responded_at "nullable"
        timestamp resolution_due_at
        timestamp resolved_at "nullable"
        timestamp closed_at "nullable"
        boolean is_sla_breached
        timestamps
    }

    COMMENTS {
        bigint id PK
        bigint ticket_id FK
        bigint user_id FK
        text body
        boolean is_internal "default false"
        timestamps
    }

    ATTACHMENTS {
        bigint id PK
        bigint ticket_id FK
        bigint user_id FK
        string file_name
        string file_path
        string file_type
        integer file_size
        timestamps
    }

    ACTIVITY_LOGS {
        bigint id PK
        bigint ticket_id FK
        bigint user_id FK
        string action "created, assigned, status_changed, commented, note_added, sla_warning"
        json metadata
        timestamps
    }
```

---

## 2. Table Specifications & Optimization Indexes

### `users`
- `id` (BIGINT UNSIGNED, Primary Key)
- `name` (VARCHAR 255)
- `email` (VARCHAR 255, Unique Index)
- `password` (VARCHAR 255)
- `role` (ENUM: `'admin'`, `'agent'`, `'requester'`, `'manager'`, Index)
- `department_id` (BIGINT UNSIGNED, Foreign Key to `departments.id`, Nullable)
- `is_active` (BOOLEAN, Default true, Index)
- `created_at`, `updated_at` (TIMESTAMPS)

### `tickets`
- `id` (BIGINT UNSIGNED, Primary Key)
- `reference_no` (VARCHAR 32, Unique Index, e.g. `'OPS-2026-1001'`)
- `title` (VARCHAR 255)
- `description` (TEXT)
- `priority` (ENUM: `'low'`, `'medium'`, `'high'`, `'critical'`, Index)
- `status` (ENUM: `'open'`, `'assigned'`, `'in_progress'`, `'pending'`, `'resolved'`, `'closed'`, Index)
- `category_id` (BIGINT UNSIGNED, Foreign Key to `categories.id`, Index)
- `requester_id` (BIGINT UNSIGNED, Foreign Key to `users.id`, Index)
- `agent_id` (BIGINT UNSIGNED, Foreign Key to `users.id`, Nullable, Index)
- `sla_policy_id` (BIGINT UNSIGNED, Foreign Key to `sla_policies.id`, Index)
- `response_due_at` (TIMESTAMP, Index)
- `responded_at` (TIMESTAMP, Nullable)
- `resolution_due_at` (TIMESTAMP, Index)
- `resolved_at` (TIMESTAMP, Nullable, Index)
- `closed_at` (TIMESTAMP, Nullable)
- `is_sla_breached` (BOOLEAN, Default false, Index)
- `created_at`, `updated_at` (TIMESTAMPS, Index on created_at for date-range reporting)

**Composite Indexes**:
- `tickets_status_priority_idx` (`status`, `priority`) -> Optimizes queue filtering and sorting.
- `tickets_agent_status_idx` (`agent_id`, `status`) -> Optimizes agent workload queries.
- `tickets_requester_created_idx` (`requester_id`, `created_at`) -> Optimizes requester dashboard queries.

### `comments`
- `id` (BIGINT UNSIGNED, Primary Key)
- `ticket_id` (BIGINT UNSIGNED, Foreign Key to `tickets.id`, Index)
- `user_id` (BIGINT UNSIGNED, Foreign Key to `users.id`, Index)
- `body` (TEXT)
- `is_internal` (BOOLEAN, Default false, Index)
- `created_at`, `updated_at` (TIMESTAMPS)

### `activity_logs`
- `id` (BIGINT UNSIGNED, Primary Key)
- `ticket_id` (BIGINT UNSIGNED, Foreign Key to `tickets.id`, Index)
- `user_id` (BIGINT UNSIGNED, Foreign Key to `users.id`, Index)
- `action` (VARCHAR 64, Index)
- `metadata` (JSON, Nullable)
- `created_at` (TIMESTAMP, Index)

---

## 3. Query Optimization & Preventing N+1 Problems

All collection endpoints use explicit eager loading (`with(...)`):
```php
Ticket::with(['requester:id,name,email', 'agent:id,name,email', 'category:id,name', 'slaPolicy:id,name,priority'])
    ->when($status, fn($q) => $q->where('status', $status))
    ->when($priority, fn($q) => $q->where('priority', $priority))
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```
Ticket details eager-load comments and audit trails in a single query:
```php
$ticket->load([
    'requester', 
    'agent', 
    'category', 
    'slaPolicy', 
    'attachments',
    'comments' => fn($q) => $isAgentOrAdmin ? $q->with('user') : $q->where('is_internal', false)->with('user'),
    'activityLogs.user'
]);
```
