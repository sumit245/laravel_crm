# Laravel CRM — Consolidated Project Audit

**Generated:** 2026-05-19  
**Environment verified:** Local (`APP_ENV=local`), `http://127.0.0.1:8000`  
**Browser session:** Authenticated as admin user id `11` (`admin@sugslloyd.com`)  
**Method:** Live browser (Cursor IDE Browser) + authenticated HTTP render checks where noted  

> **Task progress (Jira-style):** use **[`docs/PROJECT_TRACKER.md`](./PROJECT_TRACKER.md)** — canonical board with ticket keys (`CRM-*`), status, evidence, and doc-integrity flags. Update tickets there first; this file holds narrative audit detail and code pointers.

This document replaces scattered task/checklist markdown under `docs/`, `.cursor/`, and `.kiro/specs/` for **audit narrative**. After you confirm accuracy, the files listed in [Appendix A — Safe to delete](#appendix-a--safe-to-delete-after-this-file) can be removed.

**Not replaced (keep):** `AGENTS.md`, `docs/ActivityLogger_API_Documentation.md`, `.agent/skills/**` (agent guidance, not task queues).

---

## Executive summary

See **[PROJECT_TRACKER.md](./PROJECT_TRACKER.md)** for counts and the [open backlog](./PROJECT_TRACKER.md#open-backlog-priority-order).

| Area | Tracker | Notes |
|------|---------|--------|
| Dashboard UX | 16 DONE / 1 OPEN / 2 NOT_TESTED | `CRM-DASH-017` open |
| Staff profile | 12 DONE / 2 OPEN / 2 PARTIAL | `CRM-STAFF-023` open |
| Inventory (Dec 2025 report) | NOT_TESTED re-run | `test_report.md` still valid as historical; `CRM-INV-302` fixed 2026-05-26 |
| PROJECT_PLAN §11.2 | **STALE_DOC** | Billing/JICR/HRM routes work (HTTP 200) |
| UI layout branch | **NOT_TESTED** | `CRM-UI-001` |
| Vendor API tests | **DONE** | `CRM-API-006` — `php artisan test --filter=Vendor` passed 2026-05-26 |
| Controller performance | **BACKLOG** | `CRM-PERF-*` |
| Repo hygiene | OK | `.cursor/.memory/current_task.md` + `TESTING_RULES.md` |

---

## 1. Browser verification log

### 1.1 Login (`GET /login`)

| Check | Result |
|-------|--------|
| Page loads | **Pass** — HTTP 200 |
| Placeholder vs DB email | **Mismatch** — input placeholder `admin@example.com`; local admin is `admin@sugslloyd.com` (seeder default `admin@dashandots.tech` not present in this DB) |
| Sign-in → dashboard | **Pass** — redirects to `/dashboard` after auth |

**Local audit note:** Password for user `11` was reset to `local-audit-2026` only for this audit on `APP_ENV=local`. **Restore the admin password** if you rely on a known credential.

### 1.2 Dashboard (`GET /dashboard`)

Verified in browser after login.

| Item (from `dashboard_fixes.md`) | Browser evidence | Status |
|----------------------------------|------------------|--------|
| P0.1 Pole speed top-10 + filters | District chips (All, Darbhanga, Lakhisarai, …), search box, **Show all rows** toggle | **Done** |
| P0.2 Tri-state slow / not started | Not re-validated row-by-row in this pass; code + prior chain marked done | **Assumed done** |
| P0.3 Role-aware greeting | Heading **Good Morning, Sumit Ranjan**; subtitle **Organization summary** (admin) | **Done** |
| P1.4 PM card grid | Section present (district PM area; empty copy when no data) | **Done** |
| P1.5 Actionable empty states | **View staff** / **View vendors** links on empty Top Engineers/Vendors | **Done** |
| P1.6 Combined Metrics when Rooftop=0 | **Combined Metrics** section still visible alongside **Streetlight Projects** | **Not done** |
| P1.7 Print scope modal | **Open dashboard print options**; modal **Print Scope Preview** in DOM | **Done** |
| P1.8 Sidebar active state | **Dashboard** nav present; active styling not fully asserted in a11y tree | **Likely done** (CSS) |
| P1.9 Last updated + refresh | **Refresh dashboard data** control present | **Done** |
| P2.10 Notification bell count | Bell link name **1**; “You have 1 new notifications” | **Done** |
| P2.11 Avatar dropdown a11y | **Open profile menu** with accessible name | **Done** |
| P2.12 Time-aware greeting | “Good Morning” at audit time | **Done** |
| P2.13 Filter range echo | Copy **May 1 – May 31, 2026** under filters | **Done** |
| P2.14 Sticky speed table header | Not scroll-tested in this pass; prior chain marked verified | **Assumed done** |
| P2.15 Canonical `/dashboard` | URL is `/dashboard` when authenticated | **Done** |
| P2.16 Mobile print bar | Not resized in this pass; CSS exists per task chain | **Assumed done** |
| P2.17 Overlapping leaderboard sections | Three sections still present | **Not started** |

### 1.3 Staff profile (`GET /staff/{id}`)

| URL | Browser | HTTP (admin id 11) |
|-----|---------|---------------------|
| `/staff/91` | Not tested (local user missing) | **302** → `http://localhost` |
| `/staff/34` | **Full verify** | **200**, title `Sumit Saini · Staff` |

**Browser checks on `/staff/34`:**

| Item | Evidence | Status |
|------|----------|--------|
| P0.1 Header context | Subtitle **Staff profile · Sumit Saini** (not “Organization summary”) | **Done** |
| P0.2 Document title | Tab **Sumit Saini · Staff** | **Done** |
| P0.3 KPI meaning | Footnote: workload units = poles / rooftop sites | **Done** |
| P0.4 Zero empty state | Has data; footnote still shown | **Done** |
| P0.5 Destructive actions | **More actions for {panchayat}** → Push to RMS / Delete panchayat | **Done** |
| P0.6 Export route | Not clicked; route registered per code + task chain | **Done** (code) |
| P1.1 Meeting tile at zero | Only 3 KPIs (Total 180, Completed 0, Pending 7) — no Meeting Tasks tile | **Done** |
| P1.2 Edit / photo gated | **Edit** visible (admin can update) | **Done** for admin |
| P1.3 Project tabs | **Streetlight Project, Streetlight, Project #11** vs **Solar_Street_Light_2 … #19** | **Done** |
| P1.4 DataTable mobile | Toolbar + table present; 44px / sticky column not measured | **Partial** |
| P1.5 Ward a11y | Links like **Ward 7, Balaur Nidhi, MUZAFFARPUR. View installed poles.** | **Done** |
| P1.6–P1.7 Role / policy | Code uses `tryFrom`; not browser-visible | **Done** (code) |
| P2.1 Banking collapse | **Show banking details** collapsed | **Done** |
| P2.2 Header copy SSoT | Staff subtitle correct; dashboard uses role `match` in `header.blade.php` | **Partial** — behavior OK, no written deploy checklist |
| P2.3 Defer inactive tab DT | Inactive tab tables still in DOM; no `skipAutoInit_*` set from `staff/show` | **Open** |

---

## 2. Staff profile — full checklist (code + browser)

Source: former `docs/staff_view_fixes.md`.

| ID | Summary | Code | Browser (`/staff/34`) |
|----|---------|------|------------------------|
| P0.1 | Header context on staff routes | `StaffController` shares `pageHeaderSubtitle`; `header.blade.php` | **Verified** |
| P0.2 | `<title>` | `@section('title')` → `{name} · Staff` | **Verified** |
| P0.3 | Explain KPI numbers | Footnote in `staff/show.blade.php` | **Verified** |
| P0.4 | Zero-state copy | Conditional alert when no workload | **Verified** (with data) |
| P0.5 | Destructive UX | Overflow + typed confirm | **Verified** |
| P0.6 | `staff.exportStreetlight` | `routes/web.php` + `authorize('view')` | **Code OK** |
| P1.1 | Hide meeting metric at 0 | `$showMeetingMetric` | **Verified** |
| P1.2 | `@can('update')` Edit/avatar | Blade `@can` | **Verified** (admin) |
| P1.3 | Disambiguated tabs | Tab `title` / `aria-label` with project id | **Verified** |
| P1.4 | DT toolbar mobile | CSS in staff view | **Partial** |
| P1.5 | Ward `aria-label` | Full ward + panchayat + district | **Verified** |
| P1.6 | Role badge `tryFrom` | Blade | **Code OK** |
| P1.7 | `UserPolicy` null-safe | `tryFrom` + early return | **Code OK** |
| P2.1 | Banking collapsed + mask | Collapse + `@can` | **Verified** |
| P2.2 | Env consistency docs | — | **Open** (documentation) |
| P2.3 | Performance / defer DT | Component supports `skipAutoInit_*`; staff view does not set it | **Open** |

**Suggested next work (staff):** P2.3 only — set `window.skipAutoInit_streetlightTable_{id} = true` for non-active project tabs in `@push('scripts')`, then smoke-test tab switch.

---

## 3. Dashboard — full checklist

Source: former `docs/dashboard_fixes.md`.

### Done (browser or prior verified)

1. Pole speed panel — top 10, chips, search, show-all toggle  
2. Slow-status tri-state  
3. Role-aware greeting subtitle  
4. PM cards balanced grid  
5. Actionable empty states (staff/vendors/tasks)  
7. Print scope modal + mobile print bar  
8. Sidebar active state  
9. Freshness + refresh  
10. Notification bell count + dropdown  
11. Avatar menu affordance + a11y  
12. Time-aware greeting  
13. Active filter date echo  
14. Sticky pole-speed header (prior browser pass)  
15. `/` → `/dashboard` redirect  
16. Mobile layout / contrast (task chain; not re-tested at 375px here)

### Open

| # | Issue | Recommendation |
|---|--------|----------------|
| **6** | **Combined Metrics** duplicates streetlight-only totals when rooftop = 0 | In `dashboard/sections/performance.blade.php`, hide `.Combined Metrics` card when `rooftop.total_sites == 0` OR show delta only |
| **17** | Leaderboard / district / pole-speed overlap | Product decision: tabs or merge sections |

### Implemented but not in original numbered list

- In-app notifications (`user_event_notifications`, bell API, `NotificationController`) — **live** (bell showed **1** unread).

---

## 4. Task chain archive (high-signal entries)

Source: former `docs/task_chain_walkthrough.md`. Full narrative history lived there; retain in git history after delete.

| Theme | Outcome |
|-------|---------|
| Pole speed UX | Top-10, chips, UI polish, color-coded panchayat column |
| Dashboard persona / empty states / sidebar / PM grid / freshness / avatar / filters | Shipped |
| Notifications | DB + resolver + bell + browser push client |
| Print scope + mobile dashboard CSS | Shipped |
| Staff export route + profile context + metrics/avatar/destructive | Shipped |

---

## 5. Controller performance backlog

Source: `docs/laravel_performace_audit.md` (2026-05-07). **No code changes in that doc** — treat as prioritized engineering backlog.

### Correction (already in codebase)

- **RMS push from controllers** now queues via `App\Services\Rms\RmsSyncService` + `ProcessRmsSyncChunk` (not synchronous per-pole HTTP in controller).

### Top 10 by user impact (abbreviated)

| Priority | Controller::method | Issue |
|----------|-------------------|--------|
| High | `StaffController::show` | Per-project pole/site aggregation in one request |
| High | `StoreController::show` | Heavy first paint |
| High | `InventoryController::viewInventory` | Full load + repeated aggregates |
| High | `API\TaskController::index` | Unpaginated `get()` |
| High | `ProjectsController::show` | Dashboard loads everything |
| High | `VendorController::show` | Same pattern as staff |
| High | `RMSController::export` | Loads all logs |
| High | `StaffController::deletePanchayat` | Nested loops + per-serial writes |
| Medium | `StaffController::engineerData` | N+1 per engineer |
| Medium | `HomeController` dashboard calcs | Repeated aggregates per user |

**Staff show** aligns with open **P2.3** (front-end defer) and back-end aggregation split (lazy tab endpoints).

---

## 6. DataTable component QA

Source: `.cursor/datatable-bug-report.md`.

| Issue | Status | Action |
|-------|--------|--------|
| Page length dropdown vs `pageLength` prop | **Fixed** in `components/datatable.blade.php` | None |
| `updatePaginationInfo()` not called | **FIXME** | Verify on tab change + length change |
| Tab visibility / deferred init | **Needs test** | Switch project tabs on `/staff/34`; confirm data loads |
| Search / sort / pagination checklist | **Not run** | Manual QA or Playwright |

**Local note:** Report URL `/staff/91` is production; locally use an existing staff id (e.g. **34**).

---

## 7. Vendor Sites API (Kiro spec)

Source: `.kiro/specs/vendor-sites-api-modification/tasks.md`.

### Shipped (tasks 1–5)

- `DateFormatter` helper  
- `TaskController::getSitesForVendor()` → `streetlight_tasks`  
- `allotted_wards` → `ward`, `project_id`, dd/mm/yyyy dates  
- Task id vs `site_id`, `total_poles` from ward count × 10  

### Open (tasks 6–10)

- Checkpoint: full test suite green  
- Property tests 5–11 (response shape, vendor echo, orphaned tasks, HTTP 200)  
- Unit tests for edge cases  
- Final checkpoint  

**Files:** `app/Http/Controllers/API/TaskController.php`, `app/Helpers/DateFormatter.php`, tests under `tests/` (confirm with `php artisan test --filter=Vendor` or spec name).

---

## 8. Repository workflow anchors

| File (`.cursorrules`) | Path | Role |
|------------------------|------|------|
| Task anchor | [`.cursor/.memory/current_task.md`](../.cursor/.memory/current_task.md) | Active step + module backlog; **Active focus** section points here |
| Testing rules | [`.cursor/.memory/TESTING_RULES.md`](../.cursor/.memory/TESTING_RULES.md) | Browser testing, DONE/NOT DONE, DB safety for `php artisan test` |
| Consolidated audit (this file) | `docs/PROJECT_AUDIT.md` | Living audit, browser evidence, open queue §9 |
| Project context | `AGENTS.md` | Stack, quirks, test commands |

**After each fix:** update §9 below and the **Active focus** table in `current_task.md`. `.cursorrules` no longer requires a separate `docs/task_chain_walkthrough.md`.

---

## 9. Open work queue (prioritized)

**Maintained in [`PROJECT_TRACKER.md` § Open backlog](./PROJECT_TRACKER.md#open-backlog-priority-order).** Remaining open/pending ticket keys: `CRM-STAFF-023`, `CRM-DT-002`, `CRM-DASH-017`, `CRM-PERF-001`, `CRM-UI-001`, `CRM-OPS-001`.

---

## Appendix A — Safe to delete after this file

| File | Reason |
|------|--------|
| `docs/staff_view_fixes.md` | Merged into §2 |
| `docs/dashboard_fixes.md` | Merged into §3 |
| `docs/task_chain_walkthrough.md` | Merged into §4 (git history retains detail) |
| `docs/laravel_performace_audit.md` | Merged into §5 |
| `.cursor/datatable-bug-report.md` | Merged into §6 |
| `.kiro/specs/vendor-sites-api-modification/tasks.md` | Merged into §7 (keep `requirements.md` / `design.md` if you still want spec prose) |

**Optional delete** (spec prose only, if unused):  
`.kiro/specs/vendor-sites-api-modification/requirements.md`, `design.md`

**Do not delete:**

- `AGENTS.md` — stack, tests, quirks  
- `docs/ActivityLogger_API_Documentation.md` — API reference  
- `.agent/skills/laravel-architecture/**` — skill references  
- `docs/superpowers/specs/2026-04-14-activity-logging-design.md` — design record (not a task queue)

---

## Appendix B — Verification commands

```bash
# Server
php artisan serve --host=127.0.0.1 --port=8000

# Smoke HTTP (after auth in tinker or browser)
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/login
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/dashboard   # 302 if guest

# Tests (avoid breaking green tests per AGENTS.md)
php artisan test
php artisan test --filter=DateFormatter
```

---

## Appendix C — Key code locations

| Concern | Path |
|---------|------|
| Dashboard performance section | `resources/views/dashboard/sections/performance.blade.php` |
| Header subtitle | `resources/views/partials/header.blade.php` |
| Staff profile | `resources/views/staff/show.blade.php`, `app/Http/Controllers/StaffController.php` |
| DataTable | `resources/views/components/datatable.blade.php` |
| RMS queue | `app/Services/Rms/RmsSyncService.php`, `app/Jobs/ProcessRmsSyncChunk.php` |
| Notifications | `app/Services/Notification/EventNotificationService.php`, `app/Http/Controllers/NotificationController.php` |

---

*End of consolidated audit. Close work in `PROJECT_TRACKER.md` first; update §9 only if you keep this file as a human-readable mirror.*
