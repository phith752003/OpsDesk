# OpsDesk — Design System Specification

## 1. Design Tokens (Single Source of Truth)

All visual values in OpsDesk originate from declared CSS custom properties. No arbitrary values (such as `p-[13px]` or random hex codes) are allowed in presentation code.

### 1.1 Color Tokens (Light Theme Architecture)

OpsDesk implements a high-contrast, production-grade light theme with a slate-50 canvas (`#F8FAFC`), pure white container surfaces (`#FFFFFF`), slate-200 structural borders (`#E2E8F0`), and obsidian charcoal typography (`#0F172A`) for maximum legibility in mission-critical environments.

```css
:root {
  /* Surfaces & Canvas (Light Theme) */
  --color-bg-base: #F8FAFC;              /* Clean Slate-50 canvas */
  --color-bg-surface: #FFFFFF;           /* Crisp Pure White cards & sidebars */
  --color-bg-surface-raised: #F1F5F9;    /* Slate-100 for headers, filters, raised blocks */
  --color-bg-surface-hover: #F1F5F9;     /* Slate-100 for table row hover */
  --color-bg-surface-active: #E2E8F0;    /* Slate-200 pressed state */

  /* Text & Content (Slate Obsidian Palette) */
  --color-text-primary: #0F172A;         /* Slate-900 deep charcoal for primary headings and values */
  --color-text-secondary: #475569;       /* Slate-600 for descriptions, secondary labels */
  --color-text-muted: #64748B;           /* Slate-500 for captions, timestamps, placeholders */
  --color-text-inverse: #FFFFFF;         /* White text on solid color buttons */

  /* Hairlines & Borders */
  --color-border-subtle: #F1F5F9;        /* Slate-100 hairline row dividers */
  --color-border-default: #E2E8F0;       /* Slate-200 standard card and input borders */
  --color-border-strong: #CBD5E1;        /* Slate-300 active/focused boundaries */

  /* Primary Accent (Restrained Cobalt) */
  --color-accent: #2563EB;
  --color-accent-hover: #1D4ED8;
  --color-accent-subtle: #EFF6FF;        /* Blue-50 */
  --color-accent-focus: rgba(37, 99, 235, 0.2);

  /* Semantic Status Tokens (Light Theme Pastels) */
  --color-status-open-bg: #EFF6FF;
  --color-status-open-text: #1D4ED8;
  --color-status-open-border: #BFDBFE;

  --color-status-assigned-bg: #EEF2FF;
  --color-status-assigned-text: #4338CA;
  --color-status-assigned-border: #C7D2FE;

  --color-status-progress-bg: #F0F9FF;
  --color-status-progress-text: #0369A1;
  --color-status-progress-border: #BAE6FD;

  --color-status-pending-bg: #F8FAFC;
  --color-status-pending-text: #475569;
  --color-status-pending-border: #CBD5E1;

  --color-status-resolved-bg: #ECFDF5;
  --color-status-resolved-text: #047857;
  --color-status-resolved-border: #A7F3D0;

  --color-status-closed-bg: #F3F4F6;
  --color-status-closed-text: #4B5563;
  --color-status-closed-border: #D1D5DB;

  /* Priority Tokens (Light Theme) */
  --color-priority-low-bg: #ECFDF5;
  --color-priority-low-text: #047857;
  --color-priority-low-border: #A7F3D0;

  --color-priority-medium-bg: #FFFBEB;
  --color-priority-medium-text: #B45309;
  --color-priority-medium-border: #FDE68A;

  --color-priority-high-bg: #FFF7ED;
  --color-priority-high-text: #C2410C;
  --color-priority-high-border: #FED7AA;

  --color-priority-critical-bg: #FEF2F2;
  --color-priority-critical-text: #B91C1C;
  --color-priority-critical-border: #FECACA;

  /* SLA Health Tokens */
  --color-sla-healthy: #059669;
  --color-sla-warning: #D97706;
  --color-sla-breached: #DC2626;

  /* Internal Note Distinct Token */
  --color-note-internal-bg: #FFFBEB;     /* Amber-50 */
  --color-note-internal-border: #FCD34D; /* Amber-300 */
  --color-note-internal-badge: #B45309;  /* Amber-700 */
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

