# Laravel CRM — Project Context

## Stack
- PHP 8.5, Laravel (latest), MySQL via XAMPP
- macOS, `php artisan serve` on localhost:8000
- Queue: sync driver locally, database driver on shared hosting

## Test Command
```bash
php artisan test
php artisan test --filter=TestClassName
php artisan test --filter=test_method_name
```

## Known Quirks (DO NOT "FIX" THESE)
- `streelight_poles` — intentional typo in DB column/table name, matches production schema
- `ActivityLogger` — cannot be mocked; always uses real DB writes in tests
- Pre-existing failing tests exist — goal is don't break currently-green tests
- Bool fields stored as strings ("0"/"1") in some older migrations
- `fillable` gaps in some models — add fields before mass-assigning

## Architecture
- Service layer: `app/Services/` (Dashboard, Project, Task, Performance, Meeting, User, Inventory)
- Repository layer: `app/Repositories/`
- Queue jobs: `ProcessPoleImportChunk`, `ProcessTargetDeletionChunk`, `SyncPolesToRmsJob`
- Enums: `app/Enums/` (UserRole, ProjectType, TaskStatus, etc.)

## User Roles (11 total)
Administrator > Vertical Head > Project Manager > Site Engineer / Vendor / Store Incharge / Coordinator / HR Manager / Reporting Manager / Client / Review Meeting Only

## Key Models
- `Pole` — individual streetlight, has GPS + QR codes (panel_qr, battery_qr, luminary_qr)
- `StreetlightTask` — work assignment per panchayat
- `Streetlight` — panchayat/ward site
- `InventroyStreetLightModel` — typo intentional, matches table name
- `Tada` — travel/daily allowance summary

## File Storage
- S3 for avatars, candidate docs, pole photos
- Local disk for exports/backups

## Coding Conventions
- Controllers thin → delegate to Services
- Inline Schema::create in tests (no separate migration files for test setup)
- Role checks via middleware + policy, not inline `if (auth()->user()->role === ...)`
- Return JSON from API controllers, blade views from web controllers


<claude-mem-context>
# Memory Context

# [laravel_crm] recent context, 2026-05-07 3:16am GMT+5:30

Legend: 🎯session 🔴bugfix 🟣feature 🔄refactor ✅change 🔵discovery ⚖️decision 🚨security_alert 🔐security_note
Format: ID TIME TYPE TITLE
Fetch details: get_observations([IDs]) | Search: mem-search skill

Stats: 50 obs (16,171t read) | 690,704t work | 98% savings

### May 7, 2026
S58 Controller Performance Audit for Laravel CRM — Scope limited by observed session divergence from initial request toward profile page investigation (May 7 at 1:41 AM)
S57 Create and validate feature test for inventory view controller to verify per-store inventory calculations work correctly without cross-store data contamination (May 7 at 1:41 AM)
247 1:54a 🟣 Smoke test added validating multiple event types notify correct recipients
248 1:55a ✅ Notification system implementation documented in task chain walkthrough
249 " ✅ Dashboard fixes task marked complete in documentation
250 " ✅ Notification system implementation complete with all files staged
251 2:02a 🔴 Print scope modal and mobile-optimized print/export
252 2:03a 🔴 Print media isolation CSS prevents unwanted content in output
253 " ✅ Documented completion of print scope and mobile reachability fixes
254 2:04a ✅ Refined print scope modal UI with custom checkbox styling
255 2:09a 🔵 Print Scope Preview UI already exists in dashboard
256 2:19a 🔴 Mobile layout responsiveness for dashboard view
257 " 🔴 Date filter button contrast and visual hierarchy
258 " ✅ Task documentation: mobile layout and contrast improvements
261 2:20a 🔵 Network connectivity failure during impeccable audit setup
259 " 🔴 Expanded responsive breakpoint for mobile print action visibility
260 2:21a 🟣 Print Scope Preview modal implemented for export control
262 " ✅ CSS Responsive Layout Modifications Implemented for Dashboard Mobile Optimization
263 2:32a 🔵 Impeccable tool 'teach' command unavailable during preflight setup
264 2:33a 🔵 Laravel dev server not running on expected port during performance audit
265 " 🔵 Laravel artisan serve failed to bind to port 8000 due to permission restriction
266 2:34a 🔴 Mobile responsiveness for Pole Speed Analysis section
267 2:35a 🔵 Permission request to start Laravel server on port 8000 rejected by user
268 " 🔵 Laravel development server now accessible on port 8000 with authenticated routing
269 2:39a 🔵 Missing route definition: staff.exportStreetlight
270 " 🔴 Added missing staff.exportStreetlight route definition
271 2:40a ✅ Documented staff.exportStreetlight route fix in task chain walkthrough
272 2:42a 🔵 Mobile OTP Flow Implementation discovered in StaffController
273 " 🔵 Mobile OTP Implementation Security and UX Issues in StaffController
274 2:43a ✅ Staff Profile Page Refactored for Accessibility, Security, and Mobile OTP UX
275 " ✅ Staff Profile Page CSS Refactored for Accessibility, Contrast, and Mobile Responsiveness
278 2:54a ✅ Accessibility attributes added to hidden file input
279 " ✅ RMS panchayat sync refactored to async queue-based processing
280 " ✅ Generic error messages for profile picture upload failures
281 " ✅ Authorization check added to streetlight export method
282 " ✅ Conditional script loading for staff profile page performance
283 " ✅ Staff profile page accessibility overhaul with WCAG compliance
284 " ✅ Staff profile page visual redesign with improved mobile responsiveness
285 " ✅ OTP verification flow improved with conditional rendering and better UX
286 " ✅ File validation improvements with input reset on validation failure
S59 Controller Performance Audit for Laravel `/app/Http` directory; resulted in profile page fixes at `/staff/update-profile/11` with accessibility and security improvements (May 7 at 2:55 AM)
287 2:57a 🔵 Staff controller update methods and routing structure identified
288 " ✅ Added updateProfileDetails route to staff profile management
289 " 🟣 Implemented updateProfileDetails method in StaffController
290 " ✅ Added profile details form to staff profile page
291 " ✅ Added styling for profile details form and responsive layout
292 2:58a 🔵 Route registration and syntax validation confirmed
297 2:59a 🔵 Staff Profile Page Implementation with OTP-based Mobile Verification
293 3:01a ✅ Staff profile header subtitle conditional rendering
294 " ✅ Staff profile page title variable
295 " ✅ Staff view fixes documentation progress update
296 " ✅ Task chain walkthrough documentation for staff profile context fixes
298 3:02a 🔵 Missing Route and Service Failure in Staff Update Profile

Access 691k tokens of past work via get_observations([IDs]) or mem-search skill.
</claude-mem-context>