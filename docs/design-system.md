# OpsDesk — Design System Specification

## 1. Design Tokens (Single Source of Truth)

All visual values in OpsDesk originate from declared CSS custom properties. No arbitrary values (such as `p-[13px]` or random hex codes) are allowed in presentation code.

### 1.1 Color Tokens

```css
:root {
  /* Surface Foundations */
  --color-bg-base: #0B0F17;              /* Primary canvas background */
  --color-bg-surface: #111827;           /* Card and sidebar background */
  --color-bg-surface-raised: #1F2937;    /* Elevated containers, headers */
  --color-bg-surface-hover: #374151;     /* Hover state on list rows */
  --color-bg-surface-active: #4B5563;    /* Active/Pressed state */

  /* Text & Content */
  --color-text-primary: #F9FAFB;         /* Main headings and labels */
  --color-text-secondary: #9CA3AF;       /* Supporting descriptions, metadata */
  --color-text-muted: #6B7280;           /* Placeholder and inactive text */
  --color-text-inverse: #111827;         /* High-contrast text on bright buttons */

  /* Hairlines & Borders */
  --color-border-subtle: #1F2937;        /* Row separators, subtle dividers */
  --color-border-default: #374151;       /* Input borders, card boundaries */
  --color-border-strong: #4B5563;        /* Focused or active boundaries */

  /* Primary Accent (Restrained Cobalt) */
  --color-accent: #2563EB;
  --color-accent-hover: #1D4ED8;
  --color-accent-subtle: rgba(37, 99, 235, 0.15);
  --color-accent-focus: rgba(37, 99, 235, 0.35);

  /* Semantic Status Tokens */
  --color-status-open-bg: #1E293B;
  --color-status-open-text: #93C5FD;
  --color-status-open-border: #3B82F6;

  --color-status-assigned-bg: #312E81;
  --color-status-assigned-text: #C7D2FE;
  --color-status-assigned-border: #6366F1;

  --color-status-progress-bg: #1E3A8A;
  --color-status-progress-text: #93C5FD;
  --color-status-progress-border: #2563EB;

  --color-status-pending-bg: #374151;
  --color-status-pending-text: #E5E7EB;
  --color-status-pending-border: #6B7280;

  --color-status-resolved-bg: #064E3B;
  --color-status-resolved-text: #6EE7B7;
  --color-status-resolved-border: #059669;

  --color-status-closed-bg: #18181B;
  --color-status-closed-text: #A1A1AA;
  --color-status-closed-border: #3F3F46;

  /* Priority Tokens */
  --color-priority-low-bg: rgba(16, 185, 129, 0.1);
  --color-priority-low-text: #34D399;
  --color-priority-low-border: #059669;

  --color-priority-medium-bg: rgba(245, 158, 11, 0.1);
  --color-priority-medium-text: #FBBF24;
  --color-priority-medium-border: #D97706;

  --color-priority-high-bg: rgba(249, 115, 22, 0.1);
  --color-priority-high-text: #FB923C;
  --color-priority-high-border: #EA580C;

  --color-priority-critical-bg: rgba(239, 68, 68, 0.15);
  --color-priority-critical-text: #F87171;
  --color-priority-critical-border: #DC2626;

  /* SLA Health Tokens */
  --color-sla-healthy-bg: rgba(16, 185, 129, 0.12);
  --color-sla-healthy-text: #10B981;
  --color-sla-warning-bg: rgba(245, 158, 11, 0.15);
  --color-sla-warning-text: #F59E0B;
  --color-sla-breached-bg: rgba(239, 68, 68, 0.2);
  --color-sla-breached-text: #EF4444;

  /* Internal Note Distinct Token */
  --color-note-internal-bg: rgba(251, 191, 36, 0.05);
  --color-note-internal-border: rgba(245, 158, 11, 0.3);
  --color-note-internal-badge: #FBBF24;
}
```

---

## 2. Spacing Scale (4px Base Grid)

| Token | Pixels | Rem Value | Common Use Case |
|---|---|---|---|
| `--space-1` | 4px | 0.25rem | Badge padding, icon gaps |
| `--space-2` | 8px | 0.5rem | Button horizontal padding, field gap |
| `--space-3` | 12px | 0.75rem | Table cell vertical padding |
| `--space-4` | 16px | 1.0rem | Standard card padding, input padding |
| `--space-5` | 20px | 1.25rem | Section spacing |
| `--space-6` | 24px | 1.5rem | Card interior padding |
| `--space-8` | 32px | 2.0rem | Main grid gutter, header margin |
| `--space-12` | 48px | 3.0rem | Major layout block division |

---

## 3. Typography Scale & Hierarchy

- **Sans Serif**: `Inter`, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
- **Monospace**: `JetBrains Mono`, 'Fira Code', 'Courier New', monospace

| Size | Pixels | Line Height | Weight | Application |
|---|---|---|---|---|
| `text-xs` | 12px | 16px | 500 | Metadata, timestamp, badge labels |
| `text-sm` | 14px | 20px | 400 / 500 | Table cells, body copy, form inputs |
| `text-base` | 16px | 24px | 500 | Card titles, navigation items |
| `text-lg` | 18px | 28px | 600 | Page subheadings, modal titles |
| `text-xl` | 20px | 28px | 600 | Metric numbers, major headers |
| `text-2xl` | 24px | 32px | 700 | Primary view headers |

**Numeric Formatting**: All numbers, timestamps, ticket codes, and currency use `tabular-nums` (`font-variant-numeric: tabular-nums`).

---

## 4. Radii & Elevation Floor

- `--radius-sm`: 4px (Buttons, badges, tags)
- `--radius-md`: 6px (Inputs, dropdowns, tooltips)
- `--radius-lg`: 8px (Cards, modal dialogues)
- `--radius-full`: 9999px (Avatars, status pills)

**Shadows**: OpsDesk eschews heavy diffuse drop shadows in favor of 1px border strokes with subtle elevation:
- Ambient surface: `box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.35)`
- Modal popover: `box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5)`
