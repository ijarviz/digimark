---
name: Kinetic Analytics
colors:
  surface: '#f8f9fb'
  surface-dim: '#d9dadc'
  surface-bright: '#f8f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f6'
  surface-container: '#edeef0'
  surface-container-high: '#e7e8ea'
  surface-container-highest: '#e1e2e4'
  on-surface: '#191c1e'
  on-surface-variant: '#434654'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f3'
  outline: '#737685'
  outline-variant: '#c3c6d6'
  surface-tint: '#0c56d0'
  primary: '#003d9b'
  on-primary: '#ffffff'
  primary-container: '#0052cc'
  on-primary-container: '#c4d2ff'
  inverse-primary: '#b2c5ff'
  secondary: '#ba0035'
  on-secondary: '#ffffff'
  secondary-container: '#e51146'
  on-secondary-container: '#fffbff'
  tertiary: '#004c4a'
  on-tertiary: '#ffffff'
  tertiary-container: '#006663'
  on-tertiary-container: '#00ebe5'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2ff'
  primary-fixed-dim: '#b2c5ff'
  on-primary-fixed: '#001848'
  on-primary-fixed-variant: '#0040a2'
  secondary-fixed: '#ffdada'
  secondary-fixed-dim: '#ffb3b6'
  on-secondary-fixed: '#40000c'
  on-secondary-fixed-variant: '#920027'
  tertiary-fixed: '#35fbf5'
  tertiary-fixed-dim: '#00ddd8'
  on-tertiary-fixed: '#00201f'
  on-tertiary-fixed-variant: '#00504d'
  background: '#f8f9fb'
  on-background: '#191c1e'
  surface-variant: '#e1e2e4'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.01em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
  data-mono:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
    letterSpacing: 0.02em
  label-caps:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 4px
  gutter: 16px
  margin-page: 24px
  card-padding: 16px
  table-cell-padding: 12px 16px
---

## Brand & Style

The design system is engineered for high-performance internal monitoring and social media data orchestration. It prioritizes **high-density information display** and **functional efficiency** over decorative elements. 

The aesthetic is **Corporate / Modern**, utilizing a structured grid and clear visual hierarchy to reduce cognitive load when processing large datasets. The UI should evoke a sense of reliability, precision, and real-time responsiveness. This system is optimized for expert users who require a "cockpit" view of cross-platform social metrics and background process health.

## Colors

This design system utilizes a foundation of **Corporate Blue** to establish trust and professional authority. 

- **Primary:** Use for main actions, active states, and primary navigation elements.
- **Accents:** Pink and Cyan are reserved strictly for TikTok-specific metrics or platform-specific branding within cards to allow for instant visual categorization.
- **Backgrounds:** The interface uses a tiered gray system. The main canvas is `#F4F5F7`, while surface elements (cards, tables) are pure white (`#FFFFFF`) to create a clear "layering" effect without heavy shadows.
- **Status Colors:** These follow standard utility patterns for log monitoring and cron job states. Ensure high contrast against white backgrounds for accessibility.

## Typography

The system uses **Inter** exclusively to ensure maximum legibility at small sizes. The scale is intentionally compressed to accommodate high-density data tables.

- **Data Tables:** Use `body-sm` for standard cell content. For numerical IDs or job hashes, use a medium weight to simulate a mono-type feel while maintaining font consistency.
- **Headings:** Use `headline-md` for card titles.
- **Labels:** `label-caps` should be used for table headers and metadata categories to distinguish them from actionable data.

## Layout & Spacing

The design system employs a **12-column fluid grid** with a maximum container width of 1600px for ultra-wide monitoring displays. 

- **Density:** We utilize a 4px baseline grid. 
- **Grid Layout:** 24px margins on the outer viewport with 16px gutters between cards.
- **Internal Spacing:** Components use tight internal padding (12px or 16px) to maximize the amount of visible data on a single screen without scrolling. 
- **Mobile:** On mobile devices, the 12-column grid collapses to 1 column. Navigation sidebars transform into a bottom-tab bar or a condensed "hamburger" menu.

## Elevation & Depth

To maintain a clean "Admin Tool" feel, this design system avoids heavy shadows. 

- **Surface Tiers:** Background is `#F4F5F7`. Cards and Containers are pure `#FFFFFF`.
- **Borders:** Use a subtle 1px border (`#DFE1E6`) around cards and input fields instead of shadows to define boundaries.
- **Interactions:** A subtle `0px 2px 4px rgba(0, 0, 0, 0.05)` shadow is applied only on hover for clickable cards or buttons to indicate interactivity.
- **Modals:** Use a heavy background dimming (60% opacity) with a centered white container to focus attention on critical OAuth or configuration tasks.

## Shapes

The design system uses a **Soft (0.25rem)** roundedness approach. This maintains a professional, "structured" appearance while feeling modern.

- **Standard Elements:** Buttons, Input fields, and Checkboxes use a 4px radius.
- **Cards:** Use `rounded-lg` (8px) to subtly soften the large layout blocks.
- **Status Badges:** Use a fully rounded "pill" shape (100px) to distinguish them from clickable buttons.

## Components

### Data Tables
- **Header:** Use `label-caps` with a light gray background (`#F4F5F7`). 
- **Rows:** 48px fixed height for high density. Use alternating row stripes or 1px bottom borders for scanability.
- **Status Badges:** Compact pills with low-opacity backgrounds (e.g., Success: Light green background with dark green text).

### Metric Cards
- **Trend Indicators:** Small icons (Up/Down arrows) next to percentages. Use `#36B37E` for growth and `#FF5630` for decline.
- **Sparklines:** Simple 1-color line charts at the bottom of the card using the primary or platform-specific accent color.

### Form Elements
- **Inputs:** 36px height with 1px `#DFE1E6` borders. Focused state uses `primary_color_hex` with a 2px outer glow.
- **OAuth Buttons:** Branded buttons for Instagram/TikTok should follow platform brand guidelines for the icon but maintain the system's 4px border radius and height.

### Navigation
- **Sidebar:** Dark navy or neutral gray background with active states highlighted by a 4px left-side border in `primary_color_hex`.
- **Tabs:** Underline style for module switching (Instagram vs TikTok) with the active tab using the primary blue.

### Job Logs
- **Monospace Text:** Use for stack traces or raw JSON output in log monitors to ensure character alignment.