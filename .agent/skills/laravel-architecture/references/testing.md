# Testing Rules & Guidelines — Full Reference

## Table of Contents
1. [Database & Test Environment Safety](#1-database--test-environment-safety)
2. [PHPUnit Configuration](#2-phpunit-configuration)
3. [Writing Tests](#3-writing-tests)
4. [Browser Testing Requirements](#4-browser-testing-requirements)
5. [Communication Rules](#5-communication-rules)
6. [Testing Workflow](#6-testing-workflow)
7. [Existing Test Suite](#7-existing-test-suite)

---

## 1. Database & Test Environment Safety

This is the most critical section. Violations can destroy production data.

### The Golden Rule
All automated tests **must** run on an isolated test database. The `.env.testing` file and `phpunit.xml` both enforce SQLite in-memory:

```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### What `RefreshDatabase` and `migrate:fresh` Do
These will **DROP ALL TABLES** on the current connection and re-create them from migrations. This is only safe when the connection is the test SQLite connection or a dedicated test MySQL database (never production).

### Pre-flight Checklist Before Running ANY Test

1. Confirm `phpunit.xml` has:
   - `<env name="DB_CONNECTION" value="sqlite"/>`
   - `<env name="DB_DATABASE" value=":memory:"/>`
2. Confirm `.env.testing` has `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`
3. Confirm there is **no override** in the environment pointing to the production MySQL database (`sugs`)
4. If `DB_CONNECTION=mysql` and `DB_DATABASE` matches production → **STOP. Do NOT run tests.**

### Allowed Configurations

| Config | Safe? | Notes |
|--------|-------|-------|
| `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` | ✅ Preferred | In-memory, fast, disposable |
| `DB_CONNECTION=mysql`, `DB_DATABASE=sugs_test` | ✅ OK | Separate test DB, never production |
| `DB_CONNECTION=mysql`, `DB_DATABASE=sugs` | ❌ NEVER | This is production! |

### Provider Safety
In `AppServiceProvider`, global data like `states` is **not** loaded from DB when `APP_ENV=testing`. Tests receive an empty collection for `states`, avoiding unexpected reads from non-test databases.

---

## 2. PHPUnit Configuration

The `phpunit.xml` enforces these environment overrides for all test runs:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="BCRYPT_ROUNDS" value="4"/>         <!-- Faster hashing for tests -->
    <env name="CACHE_DRIVER" value="array"/>       <!-- In-memory cache -->
    <env name="DB_CONNECTION" value="sqlite"/>     <!-- SQLite, not MySQL -->
    <env name="DB_DATABASE" value=":memory:"/>     <!-- In-memory DB -->
    <env name="MAIL_MAILER" value="array"/>        <!-- No real emails -->
    <env name="PULSE_ENABLED" value="false"/>
    <env name="QUEUE_CONNECTION" value="sync"/>    <!-- Synchronous queue -->
    <env name="SESSION_DRIVER" value="array"/>     <!-- In-memory sessions -->
    <env name="TELESCOPE_ENABLED" value="false"/>
</php>
```

### Test Suites

```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
    </testsuite>
</testsuites>
```

---

## 3. Writing Tests

### Unit Tests (`tests/Unit/`)
- Test pure logic: enums, helpers, DTOs, data transformations
- Mock external dependencies (repositories, services)
- No database interaction needed
- Fast execution

```php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Enums\TaskStatus;

class TaskStatusTest extends TestCase
{
    public function test_pending_can_transition_to_in_progress(): void
    {
        $status = TaskStatus::PENDING;
        $this->assertTrue($status->canTransitionTo(TaskStatus::IN_PROGRESS));
    }

    public function test_completed_is_terminal(): void
    {
        $status = TaskStatus::COMPLETED;
        $this->assertTrue($status->isTerminal());
    }
}
```

### Feature Tests (`tests/Feature/`)
- Test HTTP endpoints, full request/response cycles
- Use `RefreshDatabase` trait for database isolation
- Use factories for test data
- Verify redirects, response codes, database state

```php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Enums\UserRole;

class ProjectCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_project(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

        $response = $this->actingAs($admin)->post('/projects', [
            'name' => 'Test Streetlight Project',
            'project_type' => 1,
            // ... other fields
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['name' => 'Test Streetlight Project']);
    }
}
```

### Property-Based Tests (Eris)
Use `giorgiosironi/eris` for properties that should hold for arbitrary inputs:

```php
use Eris\Generator;
use Eris\TestTrait;

class DateFormatterPropertyTest extends TestCase
{
    use TestTrait;

    public function test_formatted_date_is_always_valid(): void
    {
        $this->forAll(Generator\date())->then(function ($date) {
            $result = formatDate($date);
            $this->assertNotEmpty($result);
        });
    }
}
```

---

## 4. Browser Testing Requirements

### When Browser Testing Is Required
1. User explicitly requests testing via `@Browser` or mentions a specific URL
2. Fixing bugs related to form submissions, user interactions, or UI flows
3. Implementing new features involving user-facing forms
4. User reports a UI issue that requires reproducing
5. Changes to validation rules or form handling

### Browser Testing Process
1. Navigate to the exact URL provided
2. Log in with provided credentials if needed
3. Fill out forms with realistic test data
4. Test all input combinations mentioned (dropdown selections, user additions, required fields)
5. Submit forms and verify success or capture exact error messages
6. Check browser console for JavaScript errors
7. Check network requests for API call correctness
8. Verify redirects and success messages

### Login Credentials (Local Dev)
```
URL: http://localhost:8000/login
Email: admin@sugslloyd.com
Password: password123
```

### Completion Criteria
- Form submission completes successfully OR
- Exact error message captured and displayed
- **No guesswork** — only report actual browser observations
- Capture Flare links, console errors, or network errors if present

---

## 5. Communication Rules

### DONE / NOT DONE — Binary Status Only

✅ **Correct responses:**
```
Status: DONE
Browser testing completed:
- Logged in successfully with provided credentials
- Filled form with "Other" type, custom type "Standup Meeting"
- Form submitted successfully
- Redirected to /meets page
- New meeting visible in list with correct details
```

❌ **Incorrect responses:**
```
Status: Should work now
I've made the changes and it should work. There may be some issues
with validation but it probably will be fine.
```

### Prohibited Phrases
- "It should work", "There may be some problem", "It might work"
- "Probably", "Likely", "I think", "Perhaps", "Maybe"
- "Should be fine", "Might need to", "Could be"

### Required Communication Style
- "DONE" — When testing confirms functionality works
- "NOT DONE" — When testing reveals issues
- "Tested and verified: [exact result]"
- "Error observed: [exact error message]"
- Concrete facts, specific error messages, exact browser behavior

---

## 6. Testing Workflow

### Before Declaring Completion
1. Run `php artisan test` and confirm all pass
2. Test in browser if user requests it
3. Test all scenarios mentioned by user
4. Verify actual functionality, not assumed behavior
5. Capture exact error messages if failures occur

### After Making Code Changes
1. Test the changes in browser (if UI-related)
2. Verify the fix resolves the reported issue
3. Test edge cases mentioned by user
4. Re-test after fixes until success is confirmed

---

## 7. Existing Test Suite

### Feature Tests
| File | Purpose |
|------|---------|
| `VendorSitesApiPropertyTest.php` | Property-based API tests for vendor site access |
| `InventoryAddStreetlightTest.php` | Streetlight inventory creation flow |
| `StoreInventoryTest.php` | Store inventory management operations |

### Unit Tests
| File | Purpose |
|------|---------|
| `VendorSitesApiTest.php` | Vendor sites API logic |
| `DateFormatterTest.php` | Date formatting utility |
| `DateFormatterPropertyTest.php` | Property-based date tests |
| `InventroyStreetLightImportTest.php` | Streetlight inventory import logic |
