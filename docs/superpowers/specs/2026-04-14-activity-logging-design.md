# System-Wide Activity Logging Architecture

## Overview
The goal is to implement comprehensive activity logging across the entire CRM system (both Web and API controllers). Based on the agreed approach (Option B), we will strictly log **write operations** (Create, Update, Delete, Import, Dispatch, Push) and bypass simple read/search queries to ensure our Database doesn't bloat while maintaining a rigorous audit trail of all state mutations.

## Current State vs Target State
**Current State:**
- The existing `ActivityLogger` service (`ActivityLogger->log()`) is sporadically used in Web Controllers (like `PoleController`, `TasksController`, `InventoryController`).
- The API Controllers (`app/Http/Controllers/API`) do not log activities.
- Standard CRUD actions (Add, Edit, Delete) across many entities (Sites, Users, Devices, etc.) are currently missing.

**Target State (Now Completed):**
- All API and Web write endpoints rigorously hook into the `ActivityLogger`.
- Supported actions across all modules: `created`, `updated`, `deleted`, `imported`, `dispatched`, `pushed_to_rms`, `exported`, `status_changed`.
- State Deltas (Before/After values) are recorded for updates to track precise property changes.

## Architectural Approach

### 1. Refined Activity Logger Integration
Instead of writing scattered logging lines, we will integrate `$this->activityLogger->log()` robustly into the targeted controllers.

If controllers share a base class or use traits, we could move logging logic to an Eloquent Observer, but since `ActivityLogger` tracks the *Initiator* (User) and controller-specific metadata (`ip_address`, `request_id`, `batch_id`), explicitly calling it in the Controller or Service layers remains the most reliable pattern.

### 2. Implementation Scope & Status
The following areas have been fully audited and retrofitted with comprehensive logging:

- **Inventory/Stores (`InventoryController` Web & API):** All CRUD operations, Inventory imports, Device imports, Device Dispatches, Bulk Confirm Dispatch, Item Replace.
- **Sites (`SiteController` Web):** Site creation, updates, deletes, bulk Pole deletion, Pole imports.
- **Poles (`PoleController` Web):** Single and bulk deletes, RMS push triggers. 
- **Staff/Users (`StaffController` Web & API):** Staff creation, editing, deletion, bulk deletion, Staff Imports from Excel, Profile updates, password changes.
- **Tasks (`TasksController` Web):** Target/Task creation, updates, and deletes (excluding status reads).

> **Note:** Read-only paths (e.g. `index`, `search`, `show`) intentionally do not trigger activity logs to prevent DB bloating, adhering to Option B strategy.

### 3. Data Structure Convention
Every log entry follows this convention format using the existing `ActivityLog` model:
- `module`: Target logical module (e.g., `inventory`, `staff`, `store`, `site`, `task`).
- `action`: The verified verb (`created`, `updated`, `deleted`, `imported`, `dispatched`, `rms_pushed`).
- `entity`: The generic relation to the involved model (e.g., passing `$inventoryItem`).
- `changes`: Captured safely via `$this->activityLogger->diff($model)` before updates.
- `description`: A meaningful human-readable string (e.g., `"Dispatched 50 items to Site A"`).

## Trade-offs
**Explicit Logging vs Observers:** We choose explicit controller/service logging over Eloquent Observers because operations like "Bulk Imports" bypass Eloquent models entirely (using `DB::insert` or specific Import libraries) or require user/request context that Observers lack without ugly workarounds. Explicit logging guarantees context precision.

## Testing Strategy
- Tests inside `.env.testing` will assert that `ActivityLog::count()` increments correctly during Dispatch, Import, and typical CRUD.
- Verification that search/index routes **do not** increment the log.
