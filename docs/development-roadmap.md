# OpsDesk — Development Roadmap & Milestones

This document records the architectural planning milestones for the 3-month development roadmap (August – October 2026). Per git history integrity standards, all development commits maintain genuine, real-time timestamps while executing against this planned roadmap.

```mermaid
gantt
    title OpsDesk 3-Month Engineering Roadmap (2026)
    dateFormat  YYYY-MM-DD
    section August 2026: Foundation & Core Backend
    Requirements & Architecture Design   :done, 2026-08-01, 2026-08-07
    Database Schema & Migrations         :done, 2026-08-08, 2026-08-15
    Authentication & Sanctum API         :done, 2026-08-16, 2026-08-22
    Ticket CRUD & Status Workflow        :done, 2026-08-23, 2026-08-31

    section September 2026: Business Logic & Frontend
    Ticket Assignment & Communication    :done, 2026-09-01, 2026-09-07
    SLA Policies & Deadline Calculation  :done, 2026-09-08, 2026-09-14
    Audit Trails & Notifications         :done, 2026-09-15, 2026-09-21
    Vue 3 SPA & Design System Tokens     :done, 2026-09-22, 2026-09-30

    section October 2026: Optimization, QA & Release
    Dashboard Metrics & ECharts Reports  :done, 2026-10-01, 2026-10-10
    Docker Compose Containerization      :done, 2026-10-11, 2026-10-18
    9-Layer UNSLOP Redline Review        :done, 2026-10-19, 2026-10-25
    Testing, Documentation & Packaging   :done, 2026-10-26, 2026-10-31
```

---

## Milestone 1: August 2026 — Foundation & Core Backend
- **Focus**: Core data structures, authentication, and fundamental ticket state machines.
- **Deliverables**:
  - `docs/architecture.md` and `docs/database-design.md`.
  - Database migrations: `users`, `departments`, `categories`, `sla_policies`, `tickets`, `comments`, `attachments`, `activity_logs`.
  - Sanctum API token authentication and role gates.
  - Ticket reference code generator (`OPS-YYYY-XXXX`).
  - Initial PHPUnit API Feature tests.

---

## Milestone 2: September 2026 — Business Logic & Frontend
- **Focus**: Operational workflows, SLA engine, private engineering notes, and Vue 3 frontend core.
- **Deliverables**:
  - `SlaService`: priority-based target calculations, breach tracking, warning thresholds.
  - `TicketService`: transaction-safe assignment, status updates, and audit logging.
  - Vue 3 + TypeScript SPA initialization with Tailwind CSS.
  - Design tokens (`tokens.css`) adhering to the Hallmark and Unslop principles.
  - Pinia stores (`auth`, `tickets`, `sla`, `dashboard`, `users`).
  - Ticket List with real-time filtering, search, and pagination.
  - Ticket Detail view with dual-feed (Public comments vs. Private internal notes).

---

## Milestone 3: October 2026 — Optimization, QA & Release
- **Focus**: Operational analytics, ECharts data visualizations, containerization, and 9-layer review.
- **Deliverables**:
  - Operational dashboard with live metrics, agent workload matrix, and resolution trend graphs.
  - Docker Compose configuration (App, Web, MySQL 8, Redis).
  - Complete 9-layer UNSLOP Redline pass (`docs/unslop-review.md`).
  - Multi-viewport responsive visual testing (1440px, 1280px, 768px, 390px).
  - Comprehensive root `README.md` with seeded credentials and API documentation.
