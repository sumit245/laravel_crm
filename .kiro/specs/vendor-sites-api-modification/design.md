# Design Document: Vendor Sites API Modification

## Overview

This design modifies the existing `/vendor/{id}/sites` API endpoint to change its data source from the `streetlights` table to the `streetlight_tasks` table. The key changes include:

1. Querying `streetlight_tasks` joined with `streetlights` instead of querying `streetlights` directly
2. Replacing the `ward` field with `allotted_wards` from the task record
3. Formatting dates as dd/mm/yyyy instead of ISO format
4. Allowing duplicate panchayats in the response (one entry per task assignment)
5. Including `project_id` from the task record

The modification maintains backward compatibility with the existing response structure while providing task-specific information that better reflects the operational reality where the same panchayat can be assigned to a vendor through multiple tasks.

## Architecture

### Current Implementation

The current endpoint at `app/Http/Controllers/API/TaskController.php::getSitesForVendor()` uses this approach:

```php
$tasks = StreetlightTask::with('site')
    ->where('vendor_id', $vendorId)
    ->get();

$sites = $tasks->pluck('site')->filter()->unique('id')->values();
```

This fetches tasks, extracts sites, and deduplicates them by site ID, resulting in unique panchayats per vendor.

### New Implementation Architecture

The modified implementation will:

1. Query `streetlight_tasks` filtered by `vendor_id`
2. Eager load the related `site` (streetlight) relationship
3. Transform each task into a response object that combines:
   - Site information from the `streetlights` table
   - Task-specific information (`allotted_wards`, `project_id`) from `streetlight_tasks`
   - Formatted dates using a date formatter helper
4. Return all task records without deduplication

### Data Flow

```
Request: GET /vendor/{vendorId}/sites
    ↓
TaskController::getSitesForVendor($vendorId)
    ↓
Query: streetlight_tasks WHERE vendor_id = {vendorId}
    ↓
Eager Load: streetlights (via site_id foreign key)
    ↓
Transform: Map each task to response format
    ├─ Extract site fields from streetlights table
    ├─ Extract allotted_wards from streetlight_tasks
    ├─ Extract project_id from streetlight_tasks
    └─ Format created_at and updated_at dates
    ↓
Response: JSON with status, vendor_id, and sites array
```

### Technology Stack

- Framework: Laravel 10
- Database: MySQL/MariaDB
- ORM: Eloquent
- Testing: PHPUnit 10
- Property-Based Testing: Will use a PHP property-based testing library (recommendation: eris/eris or phpunit-quickcheck)

## Components and Interfaces

### 1. TaskController::getSitesForVendor()

**Location:** `app/Http/Controllers/API/TaskController.php`

**Responsibility:** Handle the API request, orchestrate data retrieval and transformation, return JSON response

**Method Signature:**
```php
public function getSitesForVendor(int $vendorId): JsonResponse
```

**Input:**
- `$vendorId` (int): The vendor user ID from the route parameter

**Output:**
- JSON response with structure:
```json
{
  "status": "success",
  "vendor_id": 123,
  "sites": [
    {
      "id": 456,
      "district_code": "01",
      "block_code": "02",
      "panchayat_code": "03",
      "state": "Bihar",
      "district": "Patna",
      "block": "Danapur",
      "panchayat": "Example Panchayat",
      "ward": "Ward 1, Ward 2",
      "mukhiya_contact": "9876543210",
      "number_of_surveyed_poles": 50,
      "number_of_installed_poles": 45,
      "created_at": "15/01/2025",
      "updated_at": "20/01/2025",
      "project_id": 789,
      "total_poles": 100
    }
  ]
}
```

**Algorithm:**
1. Query `StreetlightTask` model where `vendor_id` matches
2. Eager load the `site` relationship
3. For each task:
   - Extract site data from the related `Streetlight` model
   - Replace `ward` with `allotted_wards` from the task
   - Add `project_id` from the task
   - Format `created_at` and `updated_at` using date formatter
4. Return JSON response with all transformed records

### 2. DateFormatter Helper

**Location:** `app/Helpers/DateFormatter.php` (new file)

**Responsibility:** Convert ISO timestamp format to dd/mm/yyyy format

**Method Signature:**
```php
public static function formatToDDMMYYYY(?string $isoDate): ?string
```

**Input:**
- `$isoDate` (string|null): ISO format timestamp (e.g., "2025-01-15 10:30:45")

**Output:**
- Formatted date string (e.g., "15/01/2025") or null if input is null

**Algorithm:**
1. If input is null, return null
2. Parse the ISO date string using Carbon
3. Format as dd/mm/yyyy with leading zeros
4. Return formatted string

**Edge Cases:**
- Null input → null output
- Invalid date format → should throw exception (validation happens at database level)

### 3. StreetlightTask Model

**Location:** `app/Models/StreetlightTask.php` (existing)

**Modifications:** None required - model already has the necessary relationships and fields

**Key Relationships:**
- `site()`: BelongsTo relationship to Streetlight model via `site_id`
- `project()`: BelongsTo relationship to Project model via `project_id`

**Key Fields:**
- `vendor_id`: Foreign key to users table
- `site_id`: Foreign key to streetlights table
- `project_id`: Foreign key to projects table
- `allotted_wards`: Text field containing comma-separated ward identifiers
- `created_at`, `updated_at`: Timestamps

### 4. Streetlight Model

**Location:** `app/Models/Streetlight.php` (existing)

**Modifications:** None required

**Key Fields:**
- `id`: Primary key
- `district_code`, `block_code`, `panchayat_code`: Administrative codes
- `state`, `district`, `block`, `panchayat`: Administrative names
- `ward`: Original ward field (will be replaced in response)
- `mukhiya_contact`: Contact information
- `number_of_surveyed_poles`, `number_of_installed_poles`: Progress tracking
- `total_poles`: Target count
- `created_at`, `updated_at`: Timestamps

## Data Models

### Database Schema

**streetlight_tasks table:**
```sql
CREATE TABLE streetlight_tasks (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  site_id BIGINT UNSIGNED NOT NULL,
  engineer_id BIGINT UNSIGNED,
  vendor_id BIGINT UNSIGNED,
  manager_id BIGINT UNSIGNED,
  status ENUM('Pending', 'In Progress', 'Completed'),
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  allotted_wards TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (site_id) REFERENCES streetlights(id),
  FOREIGN KEY (vendor_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES projects(id)
)
```

**streetlights table:**
```sql
CREATE TABLE streetlights (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  project_id BIGINT UNSIGNED,
  state VARCHAR(255) NOT NULL,
  district VARCHAR(255) NOT NULL,
  block VARCHAR(255) NOT NULL,
  panchayat VARCHAR(255) NOT NULL,
  ward VARCHAR(255),
  district_code VARCHAR(255),
  block_code VARCHAR(255),
  panchayat_code VARCHAR(255),
  mukhiya_contact VARCHAR(255),
  number_of_surveyed_poles INT,
  number_of_installed_poles INT,
  total_poles INT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id)
)
```

### Data Relationships

```
StreetlightTask (N) ─── (1) Streetlight
       │
       └─── (1) Project
```

- Multiple tasks can reference the same streetlight site
- Each task belongs to one project
- Each task is assigned to one vendor (user)

### Response Data Transformation

The transformation maps database fields to API response fields:

| Response Field | Source Table | Source Field | Transformation |
|----------------|--------------|--------------|----------------|
| id | streetlights | id | Direct |
| district_code | streetlights | district_code | Direct |
| block_code | streetlights | block_code | Direct |
| panchayat_code | streetlights | panchayat_code | Direct |
| state | streetlights | state | Direct |
| district | streetlights | district | Direct |
| block | streetlights | block | Direct |
| panchayat | streetlights | panchayat | Direct |
| ward | streetlight_tasks | allotted_wards | Direct (replaces site ward) |
| mukhiya_contact | streetlights | mukhiya_contact | Direct |
| number_of_surveyed_poles | streetlights | number_of_surveyed_poles | Direct |
| number_of_installed_poles | streetlights | number_of_installed_poles | Direct |
| created_at | streetlights | created_at | Format to dd/mm/yyyy |
| updated_at | streetlights | updated_at | Format to dd/mm/yyyy |
| project_id | streetlight_tasks | project_id | Direct |
| total_poles | streetlights | total_poles | Direct |


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: No Deduplication of Tasks

*For any* vendor with multiple tasks referencing the same panchayat (same site_id), the API response should contain separate entries for each task, with the number of returned sites equal to the number of tasks assigned to that vendor.

**Validates: Requirements 1.3, 1.4**

### Property 2: Project ID Inclusion

*For any* task returned in the response, the site object should contain a `project_id` field that matches the `project_id` from the corresponding streetlight_task record.

**Validates: Requirements 1.5**

### Property 3: Allotted Wards Mapping

*For any* task with a non-null `allotted_wards` value, the response's `ward` field should exactly match the `allotted_wards` value from the task, preserving the original format including commas, spaces, and special characters.

**Validates: Requirements 2.2, 2.4**

### Property 4: Date Format Validation

*For any* non-null `created_at` or `updated_at` timestamp in the database, the formatted date in the response should match the pattern dd/mm/yyyy where day and month are two digits with leading zeros (e.g., "05/03/2025") and year is four digits.

**Validates: Requirements 3.1, 3.2, 3.4, 3.5**

### Property 5: Response Status Field

*For any* successful API request with a valid vendor ID, the response should include a `status` field with the value "success".

**Validates: Requirements 4.1**

### Property 6: Vendor ID Echo

*For any* request with vendor ID X, the response should include a `vendor_id` field with value X.

**Validates: Requirements 4.2**

### Property 7: Sites Array Structure

*For any* successful response, the response should include a `sites` field that is an array (which may be empty if the vendor has no tasks).

**Validates: Requirements 4.3**

### Property 8: Required Fields Presence

*For any* site object in the response's `sites` array, the object should contain all required fields: `id`, `district_code`, `block_code`, `panchayat_code`, `state`, `district`, `block`, `panchayat`, `ward`, `mukhiya_contact`, `number_of_surveyed_poles`, `number_of_installed_poles`, `created_at`, `updated_at`, `project_id`, `total_poles`.

**Validates: Requirements 4.4**

### Property 9: Site ID Mapping

*For any* task in the response, the `id` field in the site object should match the `site_id` from the streetlight_task record, not the task's own ID.

**Validates: Requirements 4.5**

### Property 10: Orphaned Task Exclusion

*For any* set of tasks assigned to a vendor, only tasks with valid site relationships (where the site_id references an existing streetlight record) should appear in the response.

**Validates: Requirements 5.3**

### Property 11: HTTP Status Code

*For any* valid vendor ID (whether or not they have tasks), the API should return HTTP status code 200.

**Validates: Requirements 5.4**

## Error Handling

### Invalid Vendor ID

When a vendor ID does not exist in the system:
- The API should return a 404 Not Found status or a 200 status with an error message in the response body
- The response should clearly indicate that the vendor was not found
- Implementation decision: Follow Laravel's standard resource not found pattern

### Vendor with No Tasks

When a valid vendor has no assigned tasks:
- Return HTTP 200 status
- Include `status: "success"`
- Include `vendor_id` with the requested ID
- Include `sites` as an empty array `[]`

### Orphaned Tasks

When a task references a non-existent site (data integrity issue):
- Filter out the orphaned task from the response
- Log a warning for monitoring purposes
- Continue processing other valid tasks
- Do not fail the entire request

### Null Date Values

When `created_at` or `updated_at` is null:
- The DateFormatter should return null
- The response should include the field with null value
- Do not throw an exception

### Null Allotted Wards

When `allotted_wards` is null in the task:
- Return null or empty string in the `ward` field
- Implementation decision: Use null for consistency with other nullable fields

### Database Connection Errors

When database queries fail:
- Let Laravel's exception handler manage the error
- Return appropriate 500 Internal Server Error response
- Log the error for debugging

## Testing Strategy

### Unit Testing Approach

Unit tests will focus on specific examples, edge cases, and error conditions:

1. **Example Tests:**
   - Test with a vendor that has no tasks (empty sites array)
   - Test with a non-existent vendor ID (error response)
   - Test with a vendor that has one task
   - Test with a vendor that has multiple tasks for different panchayats

2. **Edge Case Tests:**
   - Null `allotted_wards` value
   - Null `created_at` or `updated_at` timestamps
   - Orphaned task (task with invalid site_id)
   - Task with special characters in `allotted_wards`
   - Task with very long `allotted_wards` string

3. **Integration Tests:**
   - Full request/response cycle through the API endpoint
   - Database transaction rollback after each test
   - Test with real Eloquent models and relationships

### Property-Based Testing Approach

Property-based tests will verify universal properties across many generated inputs using a PHP property-based testing library. We will use **eris/eris** or **phpunit-quickcheck** for property-based testing in PHP.

**Configuration:**
- Each property test should run a minimum of 100 iterations
- Use Laravel's database factories to generate random test data
- Each test should be tagged with a comment referencing the design property

**Property Test Implementation:**

1. **Property 1: No Deduplication**
   - Generate: Random vendor with N tasks, some sharing the same site_id
   - Assert: Response sites count equals N (not unique site count)
   - Tag: `Feature: vendor-sites-api-modification, Property 1: No Deduplication of Tasks`

2. **Property 2: Project ID Inclusion**
   - Generate: Random tasks with various project_ids
   - Assert: Each response entry's project_id matches the task's project_id
   - Tag: `Feature: vendor-sites-api-modification, Property 2: Project ID Inclusion`

3. **Property 3: Allotted Wards Mapping**
   - Generate: Random allotted_wards strings (with commas, spaces, special chars)
   - Assert: Response ward field exactly matches input allotted_wards
   - Tag: `Feature: vendor-sites-api-modification, Property 3: Allotted Wards Mapping`

4. **Property 4: Date Format Validation**
   - Generate: Random dates across different years, months, days
   - Assert: Output matches regex `^\d{2}/\d{2}/\d{4}$` and represents correct date
   - Tag: `Feature: vendor-sites-api-modification, Property 4: Date Format Validation`

5. **Property 5: Response Status Field**
   - Generate: Random valid vendor IDs
   - Assert: Response contains `status: "success"`
   - Tag: `Feature: vendor-sites-api-modification, Property 5: Response Status Field`

6. **Property 6: Vendor ID Echo**
   - Generate: Random vendor IDs
   - Assert: Response vendor_id equals request vendor_id
   - Tag: `Feature: vendor-sites-api-modification, Property 6: Vendor ID Echo`

7. **Property 7: Sites Array Structure**
   - Generate: Random vendors (with and without tasks)
   - Assert: Response has `sites` field that is an array
   - Tag: `Feature: vendor-sites-api-modification, Property 7: Sites Array Structure`

8. **Property 8: Required Fields Presence**
   - Generate: Random tasks with various site data
   - Assert: Each site object has all 16 required fields
   - Tag: `Feature: vendor-sites-api-modification, Property 8: Required Fields Presence`

9. **Property 9: Site ID Mapping**
   - Generate: Random tasks with known site_ids
   - Assert: Response id field equals task's site_id (not task id)
   - Tag: `Feature: vendor-sites-api-modification, Property 9: Site ID Mapping`

10. **Property 10: Orphaned Task Exclusion**
    - Generate: Mix of valid tasks and tasks with invalid site_ids
    - Assert: Only tasks with valid sites appear in response
    - Tag: `Feature: vendor-sites-api-modification, Property 10: Orphaned Task Exclusion`

11. **Property 11: HTTP Status Code**
    - Generate: Random valid vendor IDs
    - Assert: HTTP response status is 200
    - Tag: `Feature: vendor-sites-api-modification, Property 11: HTTP Status Code`

### Test Data Generation Strategy

For property-based tests, we'll create generators for:

1. **Vendor Generator:** Creates users with role 'vendor'
2. **Site Generator:** Creates streetlight records with random administrative data
3. **Task Generator:** Creates streetlight_task records linking vendors to sites
4. **Date Generator:** Creates random timestamps across a reasonable date range
5. **Allotted Wards Generator:** Creates strings with various formats (single ward, comma-separated, with spaces, with special characters)

### Testing Tools

- **PHPUnit 10:** Primary testing framework
- **eris/eris or phpunit-quickcheck:** Property-based testing library
- **Laravel Factories:** Generate test data
- **Laravel HTTP Testing:** Test API endpoints
- **Database Transactions:** Rollback after each test

### Test Organization

```
tests/
├── Unit/
│   ├── DateFormatterTest.php          # Unit tests for date formatting
│   └── VendorSitesApiEdgeCasesTest.php # Edge case tests
├── Feature/
│   ├── VendorSitesApiTest.php         # Integration tests
│   └── VendorSitesApiPropertyTest.php # Property-based tests
└── Generators/
    ├── VendorGenerator.php
    ├── SiteGenerator.php
    ├── TaskGenerator.php
    └── AllottedWardsGenerator.php
```

### Continuous Integration

- Run all tests on every commit
- Property tests should run with 100 iterations in CI
- Consider running extended property tests (1000+ iterations) nightly
- Monitor test execution time and optimize slow tests

