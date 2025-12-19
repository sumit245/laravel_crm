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

#### Store Show → Dispatch Material tab (`/store/{id}`)

-   **Before**:
    -   Dispatch flow opened a Bootstrap modal (`projects.dispatchInventory`) from the Dispatch tab.
    -   Manual entry vs bulk upload controls were plain buttons.
    -   Bulk upload file input and instructions used a basic stacked layout, leaving large unused whitespace.
-   **After**:
    -   Dispatch form is embedded directly inside the **Dispatch Material** tab (no modal), reusing the existing dispatch logic and routes.
    -   **Vendor select** and **Entry Mode** switch are aligned on a single row using `d-flex justify-content-between`:
        -   Vendor select is a compact `form-select-sm` with a max width instead of full-width.
        -   Entry Mode uses a `form-check form-switch` that toggles between **Manual Entry** and **Bulk Upload (Excel)**.
    -   **Bulk Upload** section:
        -   Left side uses the standardized **import group** pattern (file input + green `Process Upload` button + `Download Format` link) consistent with Add Inventory, Staff, and Vendor screens.
        -   Right side uses the remaining horizontal space to show **simple text instructions** (black, small, bold labels) without borders.
        -   Layout uses `d-flex justify-content-between align-items-start gap-*` so the import controls and instructions stay aligned and responsive.
    -   Overall dispatch UI now matches the global design system for imports and uses whitespace more efficiently.
