# OpsDesk — UX Architecture & Layout Specification

## 1. Persona-Driven User Experience

### 1.1 Requester (Employee / Client)
- **Primary Goal**: Submit IT issues effortlessly and track progress with complete transparency.
- **Key Information**: "Where is my ticket? Who is working on it? Has it been resolved?"
- **Dedicated UX**:
  - Simplified navigation: My Tickets, New Request, Knowledge Base.
  - Quick Request Form: Clear single-column layout with instant category suggestions and drag-and-drop screenshots.
  - Simplified Ticket View: Shows human-readable status milestones (Submitted -> Assigned -> Work in Progress -> Resolved) rather than technical internal logs.
  - Direct reply box with automatic email/app notifications.

### 1.2 Support Agent (IT Tier 1/2/3 Specialist)
- **Primary Goal**: Process incoming ticket queue efficiently without context-switching.
- **Key Information**: "Which tickets are breaching SLA next? What internal notes did my teammate leave?"
- **Dedicated UX**:
  - High-density tabular queue with priority sorting and SLA countdown badges.
  - Split-screen Ticket Detail view:
    - Left (65% width): Problem description, attachment preview, public discussion, and private internal engineering notes.
    - Right (35% width): Action dock with status selector, priority escalation, agent assignment, and real-time SLA countdown clock.
  - Single-keystroke or quick-toggle for "Public Reply" vs. "Internal Note".

### 1.3 Operations Manager (Team Lead / IT Director)
- **Primary Goal**: Oversee queue velocity, team capacity, and SLA compliance.
- **Key Information**: "Are we meeting our 95% SLA target? Which agents are overloaded? What categories are causing the most incidents?"
- **Dedicated UX**:
  - Operational Overview Dashboard: Live SLA health gauge, open ticket count by priority, average resolution time trends.
  - Agent Workload Matrix: Capacity indicators showing active vs. resolved tickets per agent.
  - Interactive Date-Range Reporting: Exportable CSV / analytics summaries.

### 1.4 System Administrator
- **Primary Goal**: Maintain security, user lifecycle, department routing, and SLA policies.
- **Key Information**: "Who has admin access? What are the SLA response thresholds for Critical tickets?"
- **Dedicated UX**:
  - Structured User Directory with role badges and active/inactive toggles.
  - SLA Policy Configuration: Visual matrix mapping priority levels (Low, Medium, High, Critical) to target response minutes and resolution minutes.

---

## 2. Layout Standards by Pattern

### A. Data Tables (Ticket Lists, User Directory)
- Fixed header with sticky pagination footer.
- Hover states with `--color-bg-surface-hover` (`#374151`).
- Direct action columns (View, Quick Assign, Status).
- No hidden overflow; clear horizontal scrolling for compact viewports.

### B. Timelines (Activity History & Audit Trails)
- Chronological descending order (most recent on top).
- Clear icon markers distinguishing state changes, public comments, and private notes.
- Exact ISO timestamps with relative "X minutes ago" tooltips.

### C. Operational Dashboard
- Metric cards across top row (4 cards: Total Open, Avg Response Time, Avg Resolution Time, SLA Compliance Rate).
- Secondary row: 60/40 split — Left: Priority volume & status breakdown chart; Right: At-risk SLA warnings table.
- Bottom row: Recent queue activity stream.
