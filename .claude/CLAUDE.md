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
