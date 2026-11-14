# Multi-Device & Cross-Browser Responsive Layout Architecture Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transform and optimize the user/public hotel website for full responsiveness (Phones, Tablets, Laptops, PCs) while engineering dedicated, high-density desktop/laptop layouts for the Staff and Admin dashboards, ensuring seamless rendering across Google Chrome, Microsoft Edge, and Mozilla Firefox.

**Architecture:** 
- **Staff & Admin Portals (`admin.css`, `staff.css`):** Desktop-first, wide-screen workstation layout designed for screens $\ge$ 1024px. Features fixed, high-density left sidebars (260-270px), sticky topbars, multi-column metrics grids, expanded data tables with horizontal scroll containers, and cross-browser styling (standard `scrollbar-color` / `scrollbar-width` for Firefox alongside `::-webkit-scrollbar` for Chrome/Edge).
- **User Portal & Public Website (`user.css`, `public.css`, `base.css`):** Mobile-first responsive fluid grid. Features touch-friendly targets ($\ge$ 44px), collapsible drawer sidebar navigation, adaptive booking forms, stacked cards for mobile viewports ($< 768px$), and fluid hero & room carousels transitioning to multi-column desktop grids on laptop/PC screens.
- **Cross-Browser Engine Compatibility:** Prefix management and fallbacks for CSS Grid, Flexbox, backdrop-filter, date/time pickers, and custom scrollbars across Firefox (Gecko), Chrome (Blink), and Edge (Chromium).

**Tech Stack:** PHP 8.4, Vanilla CSS3 (Custom Properties, CSS Grid, Flexbox, Clamp typography), Vanilla JavaScript (ES6+), FontAwesome 6.5, Google Fonts.

---

## Global Constraints
- Strictly maintain theme design tokens in `assets/css/base.css` (`--navy`, `--gold`, `--accent`, `--border`).
- Zero reliance on external heavy CSS frameworks (no Tailwind or Bootstrap overhead) to preserve fast XAMPP load times.
- Mobile breakpoints for User portal: `< 480px` (Compact Phone), `481px - 768px` (Large Phone/Tablet Portrait), `769px - 1024px` (Tablet Landscape/Small Laptop), `> 1024px` (Desktop/Large Laptop).
- Staff & Admin workstation resolution target: Optimized for `1366x768`, `1920x1080`, and ultrawide viewports, with a clear desktop fallback container.
- Full support across modern Chrome, Edge, and Firefox without broken flex wraps or invisible elements.

---

## Review Focus
1. **Firefox Scrollbar & Form Controls:** Firefox uses `scrollbar-width: thin` and `scrollbar-color: ...` instead of `::-webkit-scrollbar`. Ensure both are present so tables and sidebars scroll smoothly in Firefox.
2. **Mobile Form Input Usability:** Check datepickers, number inputs, and proof upload buttons on mobile viewports ($\le 480px$) so they don't cause horizontal page overflow or unclickable elements.
3. **Staff/Admin Data Tables on Laptop Screens:** Tables with 8+ columns (e.g. Audit Logs, Payments, Bookings) must use `.table-responsive` wrapper with smooth native scrollbars to prevent clipping on standard 1366x768 laptops.
4. **Touch & Click Target Sizing:** Sidebar toggles, action buttons, and modal close triggers must have minimum 44px hitboxes for mobile touchscreens while retaining sleek mouse hover states on desktop.
5. **Backdrop Filter & CSS Fallbacks:** Ensure `.glass-card` and modals have opaque background fallbacks (`rgba(17, 24, 66, 0.95)`) for Firefox configurations where `backdrop-filter` is disabled or hardware-accelerated rendering differs.

---

## Tasks

### Task 1: Cross-Browser Foundation & Base Tokens (`assets/css/base.css`)
**Files:**
- Modify: `assets/css/base.css`
- Test: PHPUnit syntax check and visual verification on Chrome/Firefox/Edge engines.

- [ ] Add dual scrollbar rules: Firefox standard properties (`scrollbar-width`, `scrollbar-color`) and WebKit pseudo-elements (`::-webkit-scrollbar`).
- [ ] Implement responsive utility classes: `.table-responsive`, `.d-none-mobile`, `.d-none-desktop`, `.touch-target`.
- [ ] Add safe-area padding variables (`env(safe-area-inset-top)`, `env(safe-area-inset-bottom)`) for modern mobile notches and home indicators.
- [ ] Verify consistent box-sizing and normalize table layouts across Gecko and Blink engines.

---

### Task 2: Dedicated Desktop/Laptop Workstation Layout for Admin Portal (`assets/css/admin.css`)
**Files:**
- Modify: `assets/css/admin.css`
- Modify: `admin/dashboard.php`, `admin/bookings.php`, `admin/users.php`, `admin/payments.php`

- [ ] Set desktop container bounds: enforce spacious full-height workspace with fixed sidebar (`width: 270px; min-width: 270px;`) and fluid main content (`calc(100% - 270px)`).
- [ ] Design high-density 4-column metric card grid (`repeat(auto-fit, minmax(240px, 1fr))`) optimized for 1366px and 1920px screens.
- [ ] Enhance admin data tables (User management, Bookings matrix, Audit Logs) with sticky header rows (`position: sticky; top: 0;`), alternating subtle row shading, and horizontal scroll containment.
- [ ] Implement desktop-specific modal dialogs: centered, fixed-max-width (650px - 850px), dual-column form fields for efficient keyboard tabbing (`Tab` navigation).
- [ ] Add explicit desktop media query: if screen width is detected $< 1024px$, display an executive notice banner recommending a laptop/PC workstation while allowing collapsible fallback.

---

### Task 3: Dedicated Desktop/Laptop Workstation Layout for Staff Portal (`assets/css/staff.css`)
**Files:**
- Modify: `assets/css/staff.css`
- Modify: `staff/dashboard.php`, `staff/check-in.php`, `staff/rooms-status.php`, `staff/payments.php`

- [ ] Optimize Frontdesk Room Status Board: create responsive CSS grid for rooms (`grid-template-columns: repeat(auto-fill, minmax(260px, 1fr))`) with clear color-coded room tags (`available`, `occupied`, `maintenance`).
- [ ] Enhance Check-in & Check-out Action Desk: wide two-column split layout on laptops (Left: Today's Arrivals queue; Right: Active Stays and Departures).
- [ ] Fast-action buttons: ensure large desktop click areas for quick verification (`Verify`, `Decline`, `Check In`, `Check Out`) with instant visual feedback.
- [ ] Table horizontal scrollbars with Firefox `scrollbar-width: thin` for payment receipt triage and verification queues.

---

### Task 4: Responsive Mobile + Laptop/PC Layout for User Portal & Main Website (`assets/css/user.css`, `assets/css/public.css`)
**Files:**
- Modify: `assets/css/user.css`
- Modify: `assets/css/public.css`
- Modify: `user/dashboard.php`, `user/booking.php`, `user/payment.php`, `user/my-bookings.php`, `user/profile.php`
- Modify: `includes/components/sidebar.php`

- [ ] Mobile navigation drawer: ensure `.user-mobile-toggle` opens a smooth overlay sidebar on phone screens with backdrop blur and touch dismiss.
- [ ] Mobile-optimized cards: on screens $< 768px$, convert booking and payment tables into stacked interactive cards with prominent status badges.
- [ ] Single-column touch form layout on mobile: full-width input controls, prominent upload buttons for proof of payment, and large date-picker tap targets.
- [ ] Desktop layout on laptop/PC: when viewed on screens $\ge 1024px$, expand user portal into a multi-column dashboard with stats grid, booking cards, and AI recommendations side-by-side.
- [ ] Public website responsiveness: verify `index.php`, `rooms.php`, `about.php`, and `contact.php` scale seamlessly from 320px phone screens up to 4K desktop displays.

---

### Task 5: Cross-Browser Validation & Verification Testing
**Files:**
- Test files: `tests/Unit/FunctionsTest.php`, `tests/Unit/SessionAndSecurityTest.php`
- Inspect layout output in headless / curl checks and static HTML verification.

- [ ] Run PHP syntax checks across all modified dashboard files.
- [ ] Validate CSS rules for vendor compatibility (test Gecko `-moz-` where applicable, Blink/WebKit prefixes).
- [ ] Execute PHPUnit test suite to guarantee 100% test passing rate.
- [ ] Validate responsive HTML meta viewport tags across all dashboard templates (`<meta name="viewport" content="width=device-width, initial-scale=1.0">`).

