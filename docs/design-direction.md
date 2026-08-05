# OpsDesk — Design Direction & Brand Truth

## 1. Product Definition
**OpsDesk** is an operational IT Service Desk and Systems Management platform engineered specifically for small-to-medium businesses (SMBs). It consolidates internal incident reporting, IT hardware/software requests, SLA compliance tracking, and team workload visibility into a unified, auditable command surface.

### Target Personas
1. **Support Agent**: Handles incoming queue, diagnoses incidents, resolves tickets, records private engineering notes, and tracks individual SLA deadlines.
2. **Requester**: Submits incidents or equipment requests, tracks ticket progress in real-time, adds clarifying context, and rates resolved services.
3. **Operations Manager**: Monitors queue velocity, team capacity, department workloads, and SLA compliance percentages.
4. **System Administrator**: Oversees user lifecycle, role-based access controls, SLA policies, and organizational categories.

---

## 2. Character: Three Core Attributes
Rather than relying on generic adjectives like "modern" or "sleek", OpsDesk's visual character is defined by three precise principles:

### A. Operational Precision
- **Design translation**: High, controlled information density without visual clutter. Monospaced numeric counters and ticket codes (`OPS-2026-1042`). Tabular figures (`tabular-nums`) across all metrics, timestamps, and durations so rows scan vertically with zero layout wobble.

### B. Calm Technical Confidence
- **Design translation**: Deep slate and obsidian backgrounds (`#0B0F17`, `#111827`) paired with neutral titanium borders (`#1F2937`, `#374151`). Restrained cobalt accents (`#2563EB`) reserved strictly for primary calls to action. Absence of decorative purple gradients, holographic glows, or floating blobs.

### C. Traceable Accountability
- **Design translation**: Strict visual segregation between public requester-visible interactions and confidential internal engineering notes. High-contrast audit ribbons recording immutable timestamps and actors for every status shift.

---

## 3. World: Material & Visual Identity
- **Substrate**: Dense matte metal and dark carbon glass. Elevated cards use subtle 1px border highlights (`rgba(255, 255, 255, 0.06)`) rather than heavy diffuse drop shadows.
- **Lighting**: Flat, non-directional studio light. Surfaces are opaque and calm.
- **Micro-textures**: Crisp hairline dividers (`border-gray-800`), clean 6px corner radii on interactive components, and instantaneous focus rings with 2px offset.

---

## 4. Visual Moments: Where Richness Lives
In alignment with the **Unslop** and **Hallmark** guidelines, richness is not spread across the chrome; it is concentrated into two critical functional moments:

1. **The SLA Health Matrix & Dynamic Countdown Board**:
   - Visual gauge showing remaining response and resolution time.
   - Dynamic threshold states:
     - *Healthy* (Emerald pulse, > 2 hours)
     - *Warning / At Risk* (Amber badge with countdown timer, < 30 mins)
     - *Breached* (Crimson badge with exact overdue delta)
2. **The Chronological Audit Ledger**:
   - A dedicated vertical timeline running alongside ticket details, illustrating state transitions, actor avatars, and exact delta timestamps.

---

## 5. Product Signature Detail
**The Monospaced Ticket Code Ribbon**:
Every ticket is anchored by a high-legibility monospaced identifier badge:
```
[ OPS-2026-1042 ]  •  CRITICAL  •  SLA: 18m left
```
Set in `JetBrains Mono` / `ui-monospace`, with micro-pill indicator tags for priority and status that provide unmistakable visual recognition at 5 feet away.
