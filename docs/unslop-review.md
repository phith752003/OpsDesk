# OpsDesk — Nine-Layer UNSLOP Redline Review

This document records the comprehensive, nine-pass design audit conducted on the OpsDesk interface in strict adherence to the Design Engineering Directive and the **Unslop** & **Hallmark** frameworks.

---

## Pass 1 — Spacing
- **Objective**: Eliminate arbitrary margin/padding values; normalize to strict 4px rhythm; ensure vertical and horizontal alignment between sibling components.
- **Audit Findings**:
  - Initial templates occasionally mixed arbitrary pixel paddings.
  - Sibling cards on Dashboard and Queue needed standardized interior gutters.
- **Corrections Implemented**:
  - Normalized all padding and margin classes to standard 4px intervals: `p-2` (8px), `p-3` (12px), `p-4` (16px), `p-5` (20px), `p-6` (24px).
  - Standardized grid gaps to `gap-4` (16px) and `gap-6` (24px).
  - Aligned table header and cell paddings to `px-4 py-3.5` consistently across all data tables (`TicketListView`, `SlaMonitoringView`, `UserManagementView`).
- **Affected Components**:
  - `src/views/DashboardView.vue`
  - `src/views/TicketListView.vue`
  - `src/views/TicketDetailView.vue`
  - `src/components/common/StatCard.vue`
- **Result**: Rigid, predictable horizontal and vertical cadence throughout all screens.

---

## Pass 2 — Color & Contrast
- **Objective**: Strip out generic AI tells (purple/pink decorative neon glows, gradient text, ambient blur halos); establish controlled dark neutrals with crisp WCAG AA semantic status tokens.
- **Audit Findings**:
  - Default AI dashboards tend to default to violet/fuchsia accents and glowing card overlays.
  - Status indicators must remain distinctly identifiable across varying ambient lighting.
- **Corrections Implemented**:
  - Fixed base canvas to deep obsidian `#0B0F17` and card surfaces to dark slate `#111827` and `#1F2937`.
  - Replaced all gradient backgrounds with clean, matte dark titanium finishes.
  - Restricted action accent strictly to cobalt `#2563EB` / `#1D4ED8`.
  - Defined explicit semantic color pairs with dedicated backgrounds and borders:
    - *Open*: `bg-slate-800` / `text-blue-300` / `border-blue-500/30`
    - *Assigned*: `bg-indigo-950` / `text-indigo-300` / `border-indigo-500/30`
    - *In Progress*: `bg-blue-950` / `text-blue-400` / `border-blue-500/40`
    - *Resolved*: `bg-emerald-950` / `text-emerald-300` / `border-emerald-500/30`
    - *Critical Priority*: `bg-red-950/60` / `text-red-300` / `border-red-500/40`
    - *Internal Engineering Note*: `bg-amber-950/15` / `border-amber-500/30`
- **Affected Components**:
  - `src/assets/styles/tokens.css`
  - `src/components/common/StatusBadge.vue`
  - `src/components/common/PriorityBadge.vue`
  - `src/views/TicketDetailView.vue`
- **Result**: High-contrast, glare-free operational surface with zero gratuitous neon effects.

---

## Pass 3 — Icons & Glyph Consistency
- **Objective**: Remove emoji from controls and navigation; standardize on a single cohesive icon set with uniform stroke and bounding box.
- **Audit Findings**:
  - Emojis in operational interfaces degrade professional credibility and lack cross-platform rendering consistency.
- **Corrections Implemented**:
  - Replaced all temporary icon placeholders with Lucide icons (`lucide-vue-next`).
  - Standardized sizes to 14px (`w-3.5 h-3.5`) for inline badges, 16px (`w-4 h-4`) for navigation and action buttons, and 20px (`w-5 h-5`) for major section headers.
  - Eliminated all decorative emojis from UI buttons and navigation tabs.
- **Affected Components**:
  - `src/components/layout/AppLayout.vue`
  - `src/components/common/SlaCountdown.vue`
  - `src/views/TicketDetailView.vue`
- **Result**: Clean, technical icon hierarchy with uniform stroke weights.

---

## Pass 4 — Copy & Tone
- **Objective**: Eliminate marketing hyperbole ("Next-Gen AI Incident System", "Supercharged"); replace with precise ITIL / operations vocabulary.
- **Audit Findings**:
  - Placeholder strings were overly generic ("Something went wrong", "Item 1").
- **Corrections Implemented**:
  - Replaced marketing copy with genuine IT operations terminology:
    - "Incident Summary & Title", "SCIM Provisioning", "IKEv2 VPN Gateway", "Workstation Hostname", "SLA Target Threshold", "Confidential Internal Engineering Note".
  - Created realistic incident records in seeders (e.g., "EMEA VPN Gateway dropping packet traffic intermittently", "Battery swelling on MacBook Pro 16", "Okta SCIM 409 conflict").
- **Affected Components**:
  - `backend/database/seeders/DatabaseSeeder.php`
  - `src/views/LoginView.vue`
  - `src/views/TicketFormView.vue`
  - `src/views/SlaMonitoringView.vue`
- **Result**: Authentically reads like an enterprise IT service desk built for real sysadmins and employees.

---

## Pass 5 — Typography
- **Objective**: Establish deliberate type hierarchy; prevent numeric layout wobble; enforce tabular figures.
- **Audit Findings**:
  - Numbers and timers without fixed-width digits shifted horizontal alignment when seconds/minutes decremented.
- **Corrections Implemented**:
  - Enforced `Inter` for general UI reading and `JetBrains Mono` for all ticket codes (`OPS-2026-1042`), timestamps, countdowns, and metrics.
  - Applied `tabular-nums` (`font-variant-numeric: tabular-nums`) across all StatCards, SLA indicators, and data table columns.
  - Enforced consistent font sizes: `text-[10px]` for micro metadata, `text-xs` for tables and inputs, `text-sm` for card titles, `text-2xl` for primary view headers.
- **Affected Components**:
  - `src/assets/styles/tokens.css`
  - `src/components/common/SlaCountdown.vue`
  - `src/components/common/StatCard.vue`
  - `src/views/TicketListView.vue`
- **Result**: Zero layout jump during timer updates; immediate vertical scanability across dense records.

---

## Pass 6 — Layout & Data Density
- **Objective**: Avoid repetitive generic card grids; use tables for record processing and split-screen feeds for contextual workflows.
- **Audit Findings**:
  - Dashboards often overuse identical 4-corner cards for every piece of content.
- **Corrections Implemented**:
  - Structured the ticket queue as an information-dense data table with sticky-like headers, multi-criteria filters, and pagination.
  - Structured the Ticket Detail view as an asymmetrical 2-column workspace:
    - 65% width: Primary incident description, dual-mode reply dock, and chronological timeline.
    - 35% width: Fast status transition selector, specialist assignment, SLA countdown clock, and requester details.
  - Utilized Bento grid layout selectively on the main Dashboard to prioritize critical alert widgets.
- **Affected Components**:
  - `src/views/TicketListView.vue`
  - `src/views/TicketDetailView.vue`
  - `src/views/DashboardView.vue`
- **Result**: Ergonomic workflow efficiency tailored to high-volume support queues.

---

## Pass 7 — Surface Details & Elevation
- **Objective**: Eliminate diffuse floaty drop shadows; use deliberate 1px border strokes and crisp `:focus-visible` styling.
- **Audit Findings**:
  - Heavy drop shadows create muddy visual noise in dark themes.
- **Corrections Implemented**:
  - Replaced heavy box-shadows with crisp 1px borders (`border-border-default` = `#374151`, `border-border-subtle` = `#1F2937`).
  - Standardized corner radii to `rounded` (4px) for controls/badges and `rounded-lg` (8px) for containers.
  - Declared instant focus rings (`outline: 2px solid var(--color-accent); outline-offset: 2px;`) with zero blurry transition lag.
- **Affected Components**:
  - `src/assets/styles/tokens.css`
  - `src/components/common/Modal.vue`
  - `src/components/layout/AppLayout.vue`
- **Result**: Sharp, architectural substrate feel.

---

## Pass 8 — Visual Authenticity
- **Objective**: Power all visualizations with live backend calculations; eliminate static mock data or fabricated metrics.
- **Audit Findings**:
  - Many showcase projects render static dummy charts with hardcoded series arrays.
- **Corrections Implemented**:
  - Connected `DashboardView.vue` and `ReportsView.vue` directly to backend endpoints (`/api/v1/dashboard/metrics`, `/api/v1/reports/trends`, `/api/v1/reports/workload`).
  - ECharts graphs visualize actual 7-day incident creation/resolution ratios, priority SLA compliance percentages, and real database category counts.
- **Affected Components**:
  - `backend/app/Services/MetricService.php`
  - `src/views/ReportsView.vue`
  - `src/views/DashboardView.vue`
- **Result**: 100% empirical operational analytics reflecting actual database state.

---

## Pass 9 — Motion & Micro-Interactions
- **Objective**: Remove gratuitous animations; eliminate `transition: all`; respect `prefers-reduced-motion`.
- **Audit Findings**:
  - Animating layout properties (like `width` or `height`) or applying universal `transition: all` triggers GPU repaints and jank.
- **Corrections Implemented**:
  - Replaced all broad transitions with the targeted `.transition-subtle` utility (only animating `opacity`, `transform`, `background-color`, `border-color` with 150ms cubic-bezier).
  - Explicitly banned `transition: all`.
  - Added global `@media (prefers-reduced-motion: reduce)` rule overriding all durations to `0.01ms`.
- **Affected Components**:
  - `src/assets/styles/main.css`
  - `src/components/layout/AppLayout.vue`
  - `src/components/common/Modal.vue`
- **Result**: Instantaneous, snappy, accessible UI response with minimal rendering overhead.
