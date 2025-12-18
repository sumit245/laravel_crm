## UI Layout & Components TODOs

This file tracks micro-level UI and layout tasks for the `feature/ui-layout-consistency` branch.

### Buttons

-   Standardize primary/secondary/outline button variants across all pages.
-   Replace legacy Star Admin button colors with the chosen design system tokens.
-   Ensure icon-only buttons use `.btn-icon` / `.btn-icon-sm` consistently.

### Form Controls

-   Enforce consistent heights for `.form-control`, `.form-select`, `.input-group`, and file inputs.
-   Align label spacing, help text, and validation error styles.
-   Normalize checkbox and radio styles for tables and forms.

### Tabs

-   Use a unified `.nav-tabs.nav-tabs-modern` pattern for all tabbed sections.
-   Ensure only the active tab has the `active` class at any given time.
-   Add support for badges inside tabs to show counts where relevant.

### Sidebar & Navigation

-   Fix active state logic using route names (e.g., `Route::is('projects.*')`).
-   Prevent sidebar distortion at tablet and mobile breakpoints.
-   Ensure nested menus expand/collapse correctly.

### Footer & Layout

-   Keep footer pinned to the bottom on short pages.
-   Prevent footer from appearing mid-screen on scrollable pages.
-   Confirm `content-wrapper` height behavior is consistent across views.

### Datatables

-   Ensure header checkbox and row checkboxes are visually aligned.
-   Keep column heights and padding consistent with global table styles.
-   Verify search/filter input groups match standardized control heights.

### Select2

-   Match Select2 single and multi-select heights to `.form-control`.
-   Ensure tags/chips have consistent padding and border-radius.
-   Remove per-page Select2 overrides in favor of centralized styles.

### Responsive Issues

-   Audit `/meets/dashboard`, `/meets/details/{id}`, `/projects`, `/projects/{id}` at mobile and tablet widths.
-   Fix any overflow or clipping in filters, tabs, and datatables.
-   Ensure sidebar collapse and topbar behavior are consistent on small screens.
-   Note: CSS/layout adjustments have been implemented; browser verification on localhost is still pending.

### Page-Specific Notes

-   Record any page-specific exceptions or special cases here as they are discovered.
-   Use this section to capture \"before\" → \"after\" notes for future reference.
