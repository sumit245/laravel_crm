---
tracker_version: 1
last_verified: 2026-05-19
environment: local
app_url: http://127.0.0.1:8000
verified_as: "User id 11 (admin@sugslloyd.com)"
verification_methods:
  - browser_cursor_ide
  - authenticated_http_smoke
  - phpunit_subset
  - static_code_review
canonical: true
companion: docs/PROJECT_AUDIT.md
---

# PROJECT_TRACKER

**Canonical task board** for humans and AI agents. One row = one verifiable unit of work.

| Field | Meaning |
|-------|---------|
| **Key** | Stable id (`CRM-{AREA}-{NNN}`). Cite in commits/PRs. |
| **Status** | See [Status legend](#status-legend). |
| **Source** | Doc or spec that claimed the work. |
| **Verification** | How status was decided. |
| **Evidence** | Concrete result (URL, test output, file). |

**Rules for agents**

1. Do not set `DONE` without a verification method and evidence line.
2. If a source doc says "done" but verification fails → set `FAILED` or `PARTIAL` and add `STALE_DOC` on the source in [Doc integrity](#doc-integrity).
3. After closing a ticket: update this row, then [Open backlog](#open-backlog) counts, then `Active focus` in `.cursor/.memory/current_task.md`.
4. Narrative context (long prose) stays in `PROJECT_AUDIT.md` §1–§7; do not duplicate here.

---

## Progress snapshot

| Status | Count | Meaning |
|--------|------:|---------|
| **DONE** | 42 | Verified shipped |
| **PARTIAL** | 9 | Implemented; QA incomplete or env-specific |
| **OPEN** | 10 | Not implemented or product decision pending |
| **FAILED** | 2 | Broken vs acceptance |
| **NOT_TESTED** | 11 | Claimed in docs; no verification this pass |
| **STALE_DOC** | 6 | Doc backlog contradicts routes/code |
| **BACKLOG** | 10 | Engineering debt; no "done" claim |

*Counts are manual; re-tally when bulk-updating tickets.*

---

## Open backlog (priority order)

| # | Key | Summary | Status |
|---|-----|---------|--------|
| 1 | CRM-DASH-006 | Hide Combined Metrics when rooftop sites = 0 | DONE |
| 2 | CRM-STAFF-023 | Defer DataTable init on inactive staff project tabs | OPEN |
| 3 | CRM-DT-002 | DataTable `updatePaginationInfo` on init/tab/length change | PARTIAL |
| 4 | CRM-DASH-017 | Consolidate overlapping dashboard analytics sections | OPEN |
| 5 | CRM-API-006 | Vendor sites API test suite green (auth + property tests) | DONE |
| 6 | CRM-PERF-001 | StaffController::show — split aggregates / lazy tabs | BACKLOG |
| 7 | CRM-INV-302 | `/inventory/view` redirect host (`localhost` vs app URL) | DONE |
| 8 | CRM-AUTH-001 | Login placeholder email vs real admin email | DONE |
| 9 | CRM-UI-001 | UI layout consistency branch — browser pass at mobile/tablet | NOT_TESTED |
| 10 | CRM-OPS-001 | Restore admin password if audit temp password was set | OPEN |

---

## Status legend

| Status | Use when |
|--------|----------|
| **DONE** | Acceptance met; evidence recorded |
| **PARTIAL** | Works in code or one env; full QA missing |
| **OPEN** | Not built or no product decision |
| **FAILED** | Built but wrong / broken in verification |
| **NOT_TESTED** | No verification run (yet) |
| **CODE_ONLY** | Code/static review only |
| **STALE_DOC** | Documentation claim is outdated (ticket on doc, not feature) |
| **BACKLOG** | Known debt; not claimed complete |

---

## Doc catalog

| File | Role | Trust for task status |
|------|------|------------------------|
| `docs/PROJECT_TRACKER.md` | **This file** — source of truth | High |
| `docs/PROJECT_AUDIT.md` | Narrative audit + code pointers | Medium (defer to tracker) |
| `docs/test_report.md` | Inventory Dec 2025 browser tests | High for inv-*; contradicted on UI |
| `docs/UI_LAYOUT_TODOS.md` | UI branch checklist | Medium — CSS done, browser pending |
| `docs/PROJECT_PLAN.md` | Domain + module plan | Low for §11.2 pending (stale) |
| `docs/STAFF_VENDOR_MANAGEMENT_SUMMARY.md` | Staff/vendor tab implementation | High (Dec 2025) |
| `docs/VENDOR_SHOW_PAGE_IMPROVEMENTS.md` | Vendor show UX | Medium — not re-tested |
| `docs/README.md` | Docs index | Reference |
| `docs/observation.md` | Ad-hoc notes | Reference |
| `docs/DEBUGGING_METHODOLOGY.md` | Process | Reference |
| `docs/API_DOCUMENTATION.md` | API reference | Reference |
| `docs/ActivityLogger_API_Documentation.md` | ActivityLogger API | Reference |
| `docs/QUEUE_WORKER_SETUP.md` | Ops | Reference |
| `docs/SHARED_HOSTING_QUEUE_SETUP.md` | Ops | Reference |
| `docs/TARGET_DELETION_SETUP.md` | Ops | Reference |
| `docs/superpowers/specs/2026-04-14-activity-logging-design.md` | Design record | Reference |
| `.cursor/.memory/current_task.md` | Workflow anchor | Sync from tracker |
| `.cursor/.memory/TESTING_RULES.md` | QA rules | Process |

**Missing from `docs/` (still in repo):** `dashboard_fixes.md`, `staff_view_fixes.md`, `task_chain_walkthrough.md`, `laravel_performace_audit.md` — content merged into `PROJECT_AUDIT.md`; safe to delete per audit Appendix A.

---

## Doc integrity

| Doc | Issue | Tracker action |
|-----|--------|----------------|
| `PROJECT_PLAN.md` §11.2 | Lists Billing, JICR, HRM, Inventory as "pending route fixes" | **STALE_DOC** — routes exist; pages return HTTP 200 for admin (see CRM-MOD-*) |
| `test_report.md` header | Implies all tests passed | **STALE_DOC** — § UI Layout explicitly **NOT DONE** |
| `test_report.md` | Inventory 13/13 PASSED Dec 2025 | Still **DONE** for feature code; **NOT_TESTED** for re-run May 2026 |
| `UI_LAYOUT_TODOS.md` | "CSS implemented; browser verification pending" | Matches **CRM-UI-001** = NOT_TESTED |
| `PROJECT_AUDIT.md` | Some rows "Assumed done" | Upgraded to CODE_ONLY or PARTIAL in tracker where applicable |

---

## EPIC-DASH — Dashboard UX

| Key | Pri | Summary | Status | Source | Verification | Evidence |
|-----|-----|---------|--------|--------|--------------|----------|
| CRM-DASH-001 | P0 | Pole speed top-10, district chips, search, show-all | DONE | audit §3 | browser 2026-05-19 | `/dashboard` — chips, toggle, search |
| CRM-DASH-002 | P0 | Pole speed tri-state (`not_started` / slow / fast) | CODE_ONLY | audit §3 | code | `DashboardAnalyticsService.php` L480–517; row color in `performance.blade.php` L512 |
| CRM-DASH-003 | P0 | Role-aware greeting subtitle | DONE | audit §3 | browser | "Organization summary" for admin |
| CRM-DASH-004 | P1 | PM district card grid | DONE | audit §3 | browser | Section present |
| CRM-DASH-005 | P1 | Actionable empty states (staff/vendors links) | DONE | audit §3 | browser | Links on empty leaderboards |
| CRM-DASH-006 | P1 | Hide Combined Metrics when rooftop sites = 0 | DONE | browser 2026-05-26 | `/dashboard?project_id=19&date_filter=this_month` shows rooftop sites = 0 and no Combined Metrics heading |
| CRM-DASH-007 | P1 | Print scope preview modal | DONE | audit §3 | browser | Modal in DOM |
| CRM-DASH-008 | P1 | Sidebar active state for dashboard | PARTIAL | audit §3 | browser | Nav present; active class not fully asserted |
| CRM-DASH-009 | P1 | Last updated + refresh control | DONE | audit §3 | browser | Refresh control present |
| CRM-DASH-010 | P2 | Notification bell + unread count | DONE | audit §3 | browser | Bell showed count 1 |
| CRM-DASH-011 | P2 | Avatar menu a11y | DONE | audit §3 | browser | "Open profile menu" |
| CRM-DASH-012 | P2 | Time-aware greeting | DONE | audit §3 | browser | "Good Morning, …" |
| CRM-DASH-013 | P2 | Active filter date range echo | DONE | audit §3 | browser | May 1 – May 31, 2026 |
| CRM-DASH-014 | P2 | Sticky pole-speed table header | NOT_TESTED | audit §3 | — | CSS `position:sticky` on `th`; scroll not exercised |
| CRM-DASH-015 | P2 | Canonical `/` → `/dashboard` | DONE | audit §3 | http smoke | `GET /` → 302 `/dashboard` |
| CRM-DASH-016 | P2 | Mobile layout / print bar contrast | NOT_TESTED | audit §3 | — | Media queries exist L527–559; 375px not run |
| CRM-DASH-017 | P2 | De-duplicate overlapping leaderboard sections | OPEN | audit §3 #17 | — | Product decision |
| CRM-DASH-018 | — | In-app event notifications (bell API) | DONE | task chain | browser + code | `NotificationController`, bell API |

---

## EPIC-STAFF — Staff profile

| Key | Pri | Summary | Status | Source | Verification | Evidence |
|-----|-----|---------|--------|--------|--------------|----------|
| CRM-STAFF-001 | P0 | Header subtitle on staff routes | DONE | audit §2 | browser `/staff/34` | "Staff profile · Sumit Saini" |
| CRM-STAFF-002 | P0 | Document title `{name} · Staff` | DONE | audit §2 | browser | Tab title verified |
| CRM-STAFF-003 | P0 | KPI footnote (poles / sites) | DONE | audit §2 | browser | Footnote in view |
| CRM-STAFF-004 | P0 | Zero workload empty state | PARTIAL | audit §2 | browser | User had data; empty path not exercised |
| CRM-STAFF-005 | P0 | Destructive actions in overflow + confirm | DONE | audit §2 | browser | More actions menu |
| CRM-STAFF-006 | P0 | `staff.exportStreetlight` route + auth | CODE_ONLY | audit §2 | routes | Registered; export not clicked |
| CRM-STAFF-011 | P1 | Hide meeting KPI at zero | DONE | audit §2 | browser | 3 KPIs only |
| CRM-STAFF-012 | P1 | `@can('update')` edit/avatar | DONE | audit §2 | browser | Edit visible for admin |
| CRM-STAFF-013 | P1 | Disambiguated project tabs | DONE | audit §2 | browser | Project # in tab labels |
| CRM-STAFF-014 | P1 | DataTable mobile toolbar | PARTIAL | audit §2 | browser | Table present; 44px/sticky not measured |
| CRM-STAFF-015 | P1 | Ward link `aria-label` | DONE | audit §2 | browser | Full ward string in name |
| CRM-STAFF-016 | P1 | Role badge `UserRole::tryFrom` | CODE_ONLY | audit §2 | code | Blade/controller |
| CRM-STAFF-017 | P1 | `UserPolicy` null-safe roles | CODE_ONLY | audit §2 | code | Policy |
| CRM-STAFF-021 | P2 | Banking details collapsed + masked | DONE | audit §2 | browser | Show banking details |
| CRM-STAFF-022 | P2 | Deploy/env consistency checklist | OPEN | audit §2 | — | Documentation only |
| CRM-STAFF-023 | P2 | `skipAutoInit_*` for inactive project tabs | OPEN | audit §2 | code | No matches in `staff/show.blade.php`; component supports flag |
| CRM-STAFF-091 | — | `/staff/91` production test URL | FAILED | datatable report | http | Local: 302 (user missing); use id **34** |

---

## EPIC-INV — Inventory (WMS overhaul)

| Key | Pri | Summary | Status | Source | Verification | Evidence |
|-----|-----|---------|--------|--------|--------------|----------|
| CRM-INV-101 | — | inv-1 SIM column | NOT_TESTED | test_report | — | Dec 2025 PASS; not re-run |
| CRM-INV-102 | — | inv-2 history table | NOT_TESTED | test_report | — | idem |
| CRM-INV-104 | — | inv-4 download format | NOT_TESTED | test_report | — | Download triggered Dec 2025 |
| CRM-INV-105 | — | inv-5 bulk dispatch | NOT_TESTED | test_report | — | idem |
| CRM-INV-106 | — | inv-6 district locking | NOT_TESTED | test_report | — | idem |
| CRM-INV-107 | — | inv-7 pole inventory verify | NOT_TESTED | test_report | — | idem |
| CRM-INV-108 | — | inv-8 history service | NOT_TESTED | test_report | — | idem |
| CRM-INV-109 | — | inv-9 store policy admin-only | NOT_TESTED | test_report | — | idem |
| CRM-INV-110 | — | inv-10 PM visibility | NOT_TESTED | test_report | — | idem |
| CRM-INV-111 | — | inv-11 sidebar vs project inventory | NOT_TESTED | test_report | — | idem |
| CRM-INV-112 | — | inv-12 UI redesign | NOT_TESTED | test_report | — | idem |
| CRM-INV-113 | — | inv-13 streetlight validation | NOT_TESTED | test_report | — | idem |
| CRM-INV-301 | — | Standalone inventory index removed | DONE | route list + browser 2026-05-26 | `GET /inventory` route removed; browser reload returns 404. Inventory remains under project/store detail pages |
| CRM-INV-302 | — | `inventory/view?project_id&store_id` | DONE | phpunit 2026-05-26 | `ViewInventoryCalculationTest` passed; invalid fallback now redirects to Projects because standalone `/inventory` is removed |

---

## EPIC-DT — DataTable component

| Key | Pri | Summary | Status | Source | Verification | Evidence |
|-----|-----|---------|--------|--------|--------------|----------|
| CRM-DT-001 | — | Page length dropdown matches `pageLength` prop | DONE | audit §6 | code | `datatable.blade.php` |
| CRM-DT-002 | — | `updatePaginationInfo()` on init/change | PARTIAL | code + view cache 2026-05-26 | Removed stale FIXME; wired custom info refresh on draw/page/length/search. Browser length-change QA still pending |
| CRM-DT-003 | — | Tab switch loads deferred tables | NOT_TESTED | audit §6 | — | Manual QA on `/staff/34` |

---

## EPIC-API — Vendor sites API (Kiro)

| Key | Pri | Summary | Status | Source | Verification | Evidence |
|-----|-----|---------|--------|--------|--------------|----------|
| CRM-API-001 | — | DateFormatter helper | DONE | audit §7 | phpunit | 14 passed in DateFormatter filter |
| CRM-API-002 | — | `getSitesForVendor` schema changes | CODE_ONLY | audit §7 | code | `API/TaskController.php` |
| CRM-API-003 | — | allotted_wards → ward, dates dd/mm/yyyy | CODE_ONLY | audit §7 | code | idem |
| CRM-API-004 | — | task id vs site_id, total_poles | CODE_ONLY | audit §7 | code | idem |
| CRM-API-005 | — | DateFormatter property test (Eris) | FAILED | audit §7 | phpunit | `DateFormatterPropertyTest` BadMethodCallException |
| CRM-API-006 | — | VendorSitesApi property/feature tests | DONE | phpunit 2026-05-26 | `php artisan test --filter=Vendor` → 14 passed, 4140 assertions |

---

## EPIC-UI — Layout consistency branch

| Key | Pri | Summary | Status | Source | Verification | Evidence |
|-----|-----|---------|--------|--------|--------------|----------|
| CRM-UI-001 | — | Global buttons/forms/tabs/sidebar/footer/select2/responsive | NOT_TESTED | UI_LAYOUT_TODOS + test_report §UI | — | Doc: CSS done; browser pending |
| CRM-UI-002 | — | `/meets/dashboard`, `/meets/details`, `/projects` responsive | NOT_TESTED | UI_LAYOUT_TODOS | http only | `/meets/dashboard` 200; layout not measured |
| CRM-UI-003 | — | Store dispatch tab layout | NOT_TESTED | UI_LAYOUT_TODOS | — | — |

---

## EPIC-MOD — Module routes (PROJECT_PLAN §11.2 reconciliation)

*Doc says "pending"; brutal QA = admin HTTP smoke May 2026.*

| Key | Module | Claim in PROJECT_PLAN | Status | Verification | Evidence |
|-----|--------|----------------------|--------|--------------|----------|
| CRM-MOD-007 | Billing | Pending route fixes | **STALE_DOC** + DONE | http | `/billing/tada`, `/billing/convenience` → 200 |
| CRM-MOD-008 | JICR | Pending route fixes | **STALE_DOC** + DONE | http | `/jicr` → 200 |
| CRM-MOD-010 | HRM | Pending route fixes | **STALE_DOC** + DONE | http | `/candidates` → 200 |
| CRM-MOD-004 | Inventory | Pending CRUD | **STALE_DOC** | http + test_report | Routes exist; features claimed Dec 2025 |
| CRM-MOD-005 | Meetings | Pending verification | PARTIAL | http | `/meets/dashboard` 200; flows not exercised |
| CRM-MOD-006 | Performance | Pending detailed views | PARTIAL | http | `/dashboard` 200; overlaps CRM-DASH-017 |
| CRM-MOD-002 | Tasks & Poles | Pending | NOT_TESTED | — | Not in smoke list |
| CRM-MOD-003 | Sites | Pending | NOT_TESTED | — | — |
| CRM-MOD-009 | Backup | Pending | NOT_TESTED | — | — |
| CRM-MOD-011 | Device Import | Pending | NOT_TESTED | — | — |
| CRM-MOD-012 | RMS Export | Pending | PARTIAL | code | Queue via `RmsSyncService`; E2E not run |

---

## EPIC-PROJ — Project staff/vendor UI

| Key | Pri | Summary | Status | Source | Verification | Evidence |
|-----|-----|---------|--------|--------|--------------|----------|
| CRM-PROJ-001 | — | Modern staff tab add/remove + PM rules | DONE | STAFF_VENDOR_SUMMARY | code + doc | Dec 2025 complete |
| CRM-PROJ-002 | — | Modern vendor tab add/remove | DONE | STAFF_VENDOR_SUMMARY | code + doc | idem |
| CRM-PROJ-003 | — | Target reassignment on remove | CODE_ONLY | STAFF_VENDOR_SUMMARY | code | Controller/policy |

---

## EPIC-VEND — Vendor show page

| Key | Pri | Summary | Status | Source | Verification | Evidence |
|-----|-----|---------|--------|--------|--------------|----------|
| CRM-VEND-001 | — | Vendor show UI redesign + avatar upload | NOT_TESTED | VENDOR_SHOW_PAGE_IMPROVEMENTS | http | `/uservendors` → 200; page not visually inspected |

---

## EPIC-PERF — Controller performance (backlog)

*From `laravel_performace_audit.md` — no "done" claims.*

| Key | Area | Summary | Status |
|-----|------|---------|--------|
| CRM-PERF-001 | StaffController::show | Heavy per-project aggregation | BACKLOG |
| CRM-PERF-002 | StoreController::show | Heavy first paint | BACKLOG |
| CRM-PERF-003 | InventoryController::viewInventory | Full load + aggregates | BACKLOG |
| CRM-PERF-004 | API\TaskController::index | Unpaginated get | BACKLOG |
| CRM-PERF-005 | ProjectsController::show | Dashboard loads all | BACKLOG |
| CRM-PERF-006 | VendorController::show | Same as staff pattern | BACKLOG |
| CRM-PERF-007 | RMSController::export | Loads all logs | BACKLOG |
| CRM-PERF-008 | StaffController::deletePanchayat | Nested loops | BACKLOG |
| CRM-PERF-009 | StaffController::engineerData | N+1 | BACKLOG |
| CRM-PERF-010 | HomeController | Repeated dashboard aggregates | BACKLOG |

**Note:** RMS push refactored to queue (`RmsSyncService`, `ProcessRmsSyncChunk`) — not a CRM-PERF ticket.

---

## EPIC-AUTH — Auth / login

| Key | Pri | Summary | Status | Verification | Evidence |
|-----|-----|---------|--------|--------------|----------|
| CRM-AUTH-001 | — | Login placeholder vs real emails | DONE | phpunit 2026-05-26 | Placeholder changed to generic `Email address`; `LoginValidationTest` passed |
| CRM-AUTH-002 | — | Login → dashboard redirect | DONE | browser | Redirect after sign-in |
| CRM-AUTH-003 | — | Temp audit password on user 11 | OPEN | ops | Reset if `local-audit-2026` was applied |

---

## Verification log (2026-05-19)

### HTTP smoke (authenticated as user 11)

| Path | HTTP |
|------|------|
| `/dashboard` | 200 |
| `/staff`, `/staff/34` | 200 |
| `/projects`, `/projects/11` | 200 |
| `/billing/tada`, `/billing/convenience` | 200 |
| `/jicr` | 200 |
| `/candidates` | 200 |
| `/uservendors` | 200 |
| `/meets/dashboard` | 200 |
| `/inventory` | Removed → 404 |
| `/inventory/view?project_id=11&store_id=23` | Operational route retained for store/project inventory workflows |

### PHPUnit

```bash
php artisan test --filter=DateFormatter   # 14 passed, 1 failed (Eris property)
php artisan test --filter=Vendor          # 8 passed, 5 failed (401 API)
```

---

## Changelog

| Date | Change |
|------|--------|
| 2026-05-19 | Initial tracker; reconciled docs in `docs/` + `PROJECT_AUDIT.md` |
