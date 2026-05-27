---
name: laravel-architecture
description: Enforces Laravel project architecture and design guidelines. Use this skill WHENEVER making changes to Models, Controllers, Migrations, Routes, or any other structural component of the Laravel project. It ensures that established architectural anchors, coding standards, database strategies, and testing procedures are fully respected. Trigger this skill for ANY Laravel CRM task including creating models, writing controllers, adding routes, running migrations, writing tests, deploying, debugging, handling imports/exports, managing inventory, or working with any part of the codebase — even if the user doesn't explicitly mention "architecture".
---

# Laravel CRM — Architecture & Design Guidelines

This skill encodes the full architectural blueprint for **SLLDM** (Solar/Streetlight Lifecycle & Delivery Management), a Laravel 10 CRM running on PHP 8.1 with MySQL (production) and SQLite-in-memory (testing).

Read this document top-to-bottom when the skill triggers. For deeper reference on specific topics, consult the files in the `references/` directory as indicated.

---

## 1. Layered Architecture (Controller → Service → Repository → Model)

The project follows a strict **Controller → Service → Repository → Model** data-flow pattern. Understanding *why* each layer exists avoids the common mistake of putting business logic in controllers or raw queries in services.

```
HTTP Request
  │
  ▼
Controller              ← Validates input (FormRequest or inline), delegates to Service
  │
  ▼
Service (extends BaseService)    ← Contains business logic, wraps DB ops in transactions
  │
  ▼
Repository (extends BaseRepository) ← Eloquent query abstraction, implements RepositoryInterface
  │
  ▼
Model                   ← Eloquent model, defines relationships, $casts, $fillable
  │
  ▼
Database (MySQL / SQLite)
```

### Binding Discipline

All interfaces are bound to implementations in `App\Providers\RepositoryServiceProvider`. When creating a new Service or Repository pair:

1. Create the interface in `app/Contracts/`
2. Create the implementation in `app/Services/<Domain>/` or `app/Repositories/<Domain>/`
3. Register the binding in `RepositoryServiceProvider::register()`
4. Type-hint the interface in controller constructors — never the concrete class

### Existing Domain Bindings

| Interface | Implementation |
|-----------|---------------|
| `ProjectRepositoryInterface` | `Project\ProjectRepository` |
| `ProjectServiceInterface` | `Project\ProjectService` |
| `UserRepositoryInterface` | `User\UserRepository` |
| `UserServiceInterface` | `User\UserService` |
| `TaskRepositoryInterface` | `Task\TaskRepository` |
| `TaskServiceInterface` | `Task\TaskManagementService` |
| `TaskStateMachineInterface` | `Task\TaskStateMachine` |
| `DashboardServiceInterface` | `Dashboard\DashboardService` |
| `AnalyticsServiceInterface` | `Dashboard\AnalyticsService` |
| `MeetingRepositoryInterface` | `Meeting\MeetingRepository` |
| `MeetingServiceInterface` | `Meeting\MeetingManagementService` |
| `SiteRepositoryInterface` | `Site\SiteRepository` |
| `SiteServiceInterface` | `Site\SiteManagementService` |
| `PerformanceServiceInterface` | `Performance\PerformanceService` |
| `InventoryServiceInterface` | `Inventory\InventoryService` |

---

## 2. Enums — The Single Source of Truth

Hard-coded integers or strings for roles, statuses, project types, and phases are **forbidden**. The project has four PHP 8.1 backed enums that must always be used:

| Enum | Backing | Location | Purpose |
|------|---------|----------|---------|
| `UserRole` | `int` | `app/Enums/UserRole.php` | All user roles (ADMIN=0, SITE_ENGINEER=1, PROJECT_MANAGER=2, VENDOR=3, STORE_INCHARGE=4, COORDINATOR=5, HR_MANAGER=6, REPORTING_MANAGER=7, VERTICAL_HEAD=8, CLIENT=10, REVIEW_MEETING_ONLY=11) |
| `TaskStatus` | `string` | `app/Enums/TaskStatus.php` | Task lifecycle (Pending → In Progress → Completed / Blocked). Defines allowed transitions. |
| `ProjectType` | `int` | `app/Enums/ProjectType.php` | ROOFTOP_SOLAR=0, STREETLIGHT=1. Determines which site/task/inventory models to use. |
| `InstallationPhase` | `string` | `app/Enums/InstallationPhase.php` | Pole installation lifecycle (Not Started → In Progress → Completed). |

**Example — correct vs. incorrect:**
```php
// ✅ Correct
if ($user->role === UserRole::ADMIN->value) { ... }
$options = UserRole::options(); // for dropdowns

// ❌ Incorrect — never do this
if ($user->role === 0) { ... }
if ($user->role === 'admin') { ... }
```

When adding a new enum value, implement `label()`, `color()`, and `options()` methods following the established pattern.

---

## 3. SOLID Principles — Production Code Standards

Every piece of code written in this project must follow SOLID principles. Here's what that means concretely in this codebase:

### Single Responsibility
- Controllers handle HTTP concerns only (validation, delegation, response).
- Views handle only frontend render and should avoid heavy javascript or ajax.
- Services handle business logic and orchestration.
- Repositories handle data access exclusively.
- Import classes (Maatwebsite/Excel) handle data parsing and transformation only.

### Open/Closed
- Use the Strategy pattern for project-type-specific behavior (see `ProjectType::inventoryModelClass()`, `siteModelClass()`, `taskModelClass()`).
- Extend `BaseService` and `BaseRepository` — don't modify them for domain-specific needs.

### Liskov Substitution
- All Repository implementations fulfill the `RepositoryInterface` contract.
- All Service implementations fulfill their respective interface contracts.

### Interface Segregation
- Domain-specific interfaces extend the base marker `ServiceInterface`.
- Each domain has its own repository interface (e.g., `TaskRepositoryInterface`) — don't bloat `RepositoryInterface`.

### Dependency Inversion
- Controllers depend on interfaces, never concrete classes.
- Service constructors receive repository interfaces via DI from the IoC container.

---

## 4. Anti-Hallucination Rules

These are the most critical rules to prevent generating incorrect code.

### 4.1 No Guessing Schema
Before writing any Eloquent query, Migration, or Factory, **read the corresponding Model file AND Migration file** to verify column names, types, and cast definitions. Use `php artisan tinker` and `Schema::getColumnListing('table')` when in doubt.

- Do NOT assume `meet_time` is a datetime if it might be a string. Check `$casts`.
- Do NOT assume a column exists. Verify in the migration.

### 4.2 No Assumptions
Never say "This should fix it." Only say "I have applied the fix. Now we must verify." Report outcomes as **DONE** or **NOT DONE** with concrete evidence.

### 4.3 Read First
When a user says "It is not working," assume your previous context was wrong. Re-read the relevant file(s) from disk immediately.

### 4.4 Browser Is King
For UI tasks (Forms, Buttons, Modals), confirm the HTML structure and visual dimensions match the Controller's expectation before considering it done.

### 4.5 Prohibited Language
Do not use: "I apologize", "I assume", "Rest assured", "It should work", "There may be some problem", "Probably", "Likely", "Maybe", "Perhaps". Instead, provide factual observations and concrete verification results.

---

## 5. Testing Strategy

> **Read `references/testing.md` for the full testing rules, checklists, and examples.**

### 5.1 Environment Isolation (CRITICAL)

All automated tests run against `.env.testing`, which uses **SQLite in-memory**:

```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
QUEUE_CONNECTION=sync
CACHE_DRIVER=file
```

`phpunit.xml` also enforces these overrides. Before running `php artisan test`:
1. Confirm `phpunit.xml` has `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`
2. Confirm there is **no override** pointing tests to the production MySQL database (`sugs`)
3. If `DB_CONNECTION=mysql` and `DB_DATABASE` matches production → **STOP. Do not run tests.**

### 5.2 Test Organization

```
tests/
├── Unit/        ← Pure logic tests (enums, helpers, DTOs, transformations)
├── Feature/     ← HTTP tests (controller endpoints, import flows, API responses)
├── TestCase.php ← Base class, uses CreatesApplication trait
└── CreatesApplication.php
```

### 5.3 Running Tests

```bash
# Run all tests (uses .env.testing + phpunit.xml overrides)
php artisan test

# Run specific suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test --filter=InventoryAddStreetlightTest
```

### 5.4 Writing New Tests

- **Unit tests**: Test enums, DTOs, helper methods, service logic in isolation. Mock repositories.
- **Feature tests**: Use `RefreshDatabase` trait. Test HTTP endpoints, form submissions, import/export flows.
- `AppServiceProvider` returns an empty collection for `states` when `APP_ENV=testing` — tests don't read from non-test databases.
- Use Faker (`fakerphp/faker`) for realistic test data.
- Use Eris (`giorgiosironi/eris`) for property-based testing when applicable.

### 5.5 Existing Test Coverage

| Test | Type | What it covers |
|------|------|----------------|
| `VendorSitesApiPropertyTest` | Feature | Property-based tests for vendor sites API |
| `VendorSitesApiTest` | Unit | Vendor sites API unit tests |
| `InventoryAddStreetlightTest` | Feature | Streetlight inventory creation flow |
| `StoreInventoryTest` | Feature | Store inventory management |
| `DateFormatterTest` | Unit | Date formatting utilities |
| `DateFormatterPropertyTest` | Unit | Property-based date formatter tests |
| `InventroyStreetLightImportTest` | Unit | Streetlight inventory import logic |

---

## 6. Project Domain Knowledge

> **Read `references/domain.md` for full module documentation, user journeys, and data flows.**

### 6.1 What This System Is

A Solar Project Management CRM managing the full lifecycle of streetlight and rooftop solar installations across India's administrative hierarchy: **State → District → Block → Panchayat → Ward → Pole**.

### 6.2 Two Project Types

| Feature | Rooftop Solar (`ProjectType::ROOFTOP_SOLAR`) | Streetlight (`ProjectType::STREETLIGHT`) |
|---------|----------------------------------------------|------------------------------------------|
| Site Model | `Site` | `Streetlight` |
| Task Model | `Task` | `StreetlightTask` |
| Inventory Model | `Inventory` | `InventroyStreetLightModel` |
| Geographic depth | State → District → Site | State → District → Block → Panchayat → Ward → Pole |
| Store requirement | Optional | Required (district-based) |
| Item codes | Generic | SL01=Panel, SL02=Luminary, SL03=Battery, SL04=Structure |

### 6.3 Key Business Rules

- **Target Reassignment**: When staff/vendor removed from project → targets auto-reassign to PM (or Admin if no PM).
- **District-Based Inventory Locking**: Streetlight items locked to dispatched district; cross-district consumption blocked.
- **Role-Based Assignment**: Admins can assign anyone; PMs can only assign their `manager_id` direct reports.
- **Task State Machine**: Pending → In Progress → Completed/Blocked. Transitions validated via `TaskStatus::canTransitionTo()`.

### 6.4 Models Reference (36 models)

Key models and their relationships:
- `User` → has many Projects, Tasks, Poles, Meets
- `Project` → has many Sites/Streetlights, Tasks, Users (staff/vendors), Stores
- `Site` → belongs to Project, has many Tasks
- `Streetlight` → belongs to Project, has many StreetlightTasks, Poles
- `Pole` → belongs to StreetlightTask, Streetlight; links to Inventory items
- `StreetlightTask` → belongs to Project, Streetlight; assigned to User (engineer + vendor)
- `Inventory` / `InventroyStreetLightModel` → tracks materials per store
- `Meet` → has many DiscussionPoints, FollowUps, Users (participants)

---

## 7. Coding Standards

### 7.1 Controllers
- Prefer resource controllers (`Route::resource()`).
- Group related routes with `Route::prefix()->name()->group()`.
- Validate in the controller or use FormRequest classes in `app/Http/Requests/`.
- Return views for web routes, JSON for API routes.
- Keep controller methods lean — delegate to services.

### 7.2 Routes
- Web routes: `routes/web.php` (339 lines, well-organized by module).
- API routes: `routes/api.php` (Sanctum + Passport auth).
- Prefer Controllers over closures. The few closures that exist are for trivial static pages (`privacy-policy`, `terms-and-conditions`) or dev-only test endpoints.

### 7.3 Validation
- Validation rules must exactly match database constraints.
- Use FormRequests for complex validation (see `app/Http/Requests/`).
- For inline validation, use `$request->validate([...])`.

### 7.4 Logging
```php
// ✅ Correct — structured arrays
Log::info('Pole imported', ['pole_id' => $pole->id, 'site_id' => $siteId]);
Log::error('Import failed', ['error' => $e->getMessage(), 'row' => $row]);

// ❌ Incorrect — string concatenation
Log::info('Pole imported: ' . $pole->id . ' for site: ' . $siteId);
```

### 7.5 Database Transactions
Use `BaseService::executeInTransaction()` for multi-step operations:
```php
return $this->executeInTransaction(function () use ($data) {
    $project = $this->repository->create($data);
    // ... additional operations
    return $project;
});
```

### 7.6 Import/Export
- Uses `maatwebsite/excel` v3.1.
- Import classes in `app/Imports/` (11 import classes).
- Export classes in `app/Exports/` (4 export classes).
- For large imports (millions of rows), use chunked reading and queue jobs (`ProcessPoleImportChunk`).

### 7.7 File Storage
- AWS S3 for avatars, documents, application materials (`AWS_BUCKET=sugslloyd`).
- Local disk for temporary files.
- Use `Storage::disk('s3')` for S3 operations; `Storage::disk('local')` for local.

---

## 8. Deployment

### 8.1 Stack
- **Local Dev**: XAMPP (Apache + MySQL), `php artisan serve`
- **Production**: EC2 (Nginx), MySQL, Supervisor for queue workers
- **Queue**: `QUEUE_CONNECTION=database` in production, `sync` in testing

### 8.2 Deployment Checklist
```bash
# On the server
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
sudo supervisorctl restart laravel-worker:*
```

### 8.3 Queue Workers
Production uses Supervisor to run queue workers. Config lives in `deployment/queue-worker-supervisor.conf`.

> **Read `references/deployment.md` for the full queue worker setup, troubleshooting, and production checklist.**

### 8.4 Permissions (EC2)
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 9. Key Dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^10.10 | Core framework |
| `laravel/passport` | ^11.8 | API authentication (OAuth2) |
| `laravel/sanctum` | ^3.3 | SPA/token authentication |
| `laravel/ui` | ^4.6 | Auth scaffolding, Bootstrap |
| `spatie/laravel-permission` | * | Role & permission management |
| `maatwebsite/excel` | ^3.1 | Excel import/export |
| `barryvdh/laravel-dompdf` | ^3.1 | PDF generation (JICR reports) |
| `league/flysystem-aws-s3-v3` | ^3.29 | AWS S3 file storage |
| `smalot/pdfparser` | ^2.11 | PDF text extraction |
| `phpunit/phpunit` | ^10.1 | Testing framework |
| `giorgiosironi/eris` | dev-master | Property-based testing |
| `laravel/pint` | ^1.0 | Code style (PSR-12) |

---

## 10. Integration Points

| System | Purpose | Config |
|--------|---------|--------|
| AWS S3 | File storage (avatars, documents) | `AWS_*` env vars |
| WhatsApp API | Notifications via AiSensy | `WHATSAPP_API_URL`, `WHATSAPP_API_KEY` |
| RMS (Remote Monitoring) | Push pole data for remote monitoring | `RMSController`, `SyncPolesToRmsJob` |
| Email (SMTP) | Recruitment emails, notifications | `MAIL_*` env vars (Hostinger SMTP) |

---

## 11. Workflow Anchors

If the project has `.cursor/.memory/current_task.md` or `.cursor/.memory/TESTING_RULES.md`:
1. **Check the anchor** at the start of every turn — read `.cursor/.memory/current_task.md` to know your current step.
2. **Update the anchor** when a step completes.
3. **Check testing rules** before declaring a task finished — verify against `.cursor/.memory/TESTING_RULES.md`.

---

## 12. Reference Files

Read these when you need deeper context:

- **`references/testing.md`** — Full testing rules, safety checklists, browser testing protocol, communication standard
- **`references/domain.md`** — All 12 modules with user journeys and mermaid data-flow diagrams
- **`references/deployment.md`** — Queue worker setup (Supervisor/systemd/Windows), production checklist, troubleshooting
