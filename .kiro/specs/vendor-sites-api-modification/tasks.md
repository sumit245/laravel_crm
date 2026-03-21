# Implementation Plan: Vendor Sites API Modification

## Overview

This implementation modifies the existing `/vendor/{id}/sites` API endpoint to change its data source from unique sites to all streetlight tasks, replace ward information with task-specific allotted wards, and format dates as dd/mm/yyyy. The implementation maintains backward compatibility with the existing response structure while allowing duplicate panchayats (one entry per task).

## Tasks

- [x] 1. Create DateFormatter helper class
  - Create `app/Helpers/DateFormatter.php` with static method `formatToDDMMYYYY()`
  - Implement date conversion from ISO timestamp to dd/mm/yyyy format
  - Handle null input by returning null
  - Use Carbon for date parsing and formatting with leading zeros
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 1.1 Write property test for DateFormatter
  - **Property 4: Date Format Validation**
  - **Validates: Requirements 3.1, 3.2, 3.4, 3.5**
  - Generate random dates and verify output matches dd/mm/yyyy pattern with leading zeros

- [x] 1.2 Write unit tests for DateFormatter edge cases
  - Test null input returns null
  - Test various dates (single digit days/months, year boundaries)
  - Test leap year dates
  - _Requirements: 3.3_

- [x] 2. Modify TaskController::getSitesForVendor() to query streetlight_tasks
  - Update query in `app/Http/Controllers/API/TaskController.php`
  - Change from querying unique sites to querying all streetlight_tasks by vendor_id
  - Eager load the `site` relationship using `with('site')`
  - Remove the deduplication logic (`.unique('id')`)
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 2.1 Write property test for no deduplication
  - **Property 1: No Deduplication of Tasks**
  - **Validates: Requirements 1.3, 1.4**
  - Generate vendor with multiple tasks sharing same site_id
  - Verify response count equals task count, not unique site count

- [x] 3. Update response transformation to use allotted_wards
  - Modify the site object mapping in TaskController
  - Replace `ward` field with `allotted_wards` from streetlight_tasks table
  - Preserve original comma-separated format without modification
  - Handle null allotted_wards by returning null in ward field
  - _Requirements: 2.1, 2.2, 2.3, 2.4_

- [x] 3.1 Write property test for allotted wards mapping
  - **Property 3: Allotted Wards Mapping**
  - **Validates: Requirements 2.2, 2.4**
  - Generate random allotted_wards strings with commas, spaces, special characters
  - Verify response ward field exactly matches input allotted_wards

- [x] 3.2 Write unit test for null allotted_wards
  - Test that null allotted_wards returns null in ward field
  - _Requirements: 2.3_

- [x] 4. Add project_id to response transformation
  - Include `project_id` from streetlight_tasks table in each site object
  - Ensure project_id is extracted from the task record, not the site record
  - _Requirements: 1.5_

- [x] 4.1 Write property test for project ID inclusion
  - **Property 2: Project ID Inclusion**
  - **Validates: Requirements 1.5**
  - Generate tasks with various project_ids
  - Verify each response entry's project_id matches the task's project_id

- [x] 5. Integrate DateFormatter into response transformation
  - Apply DateFormatter::formatToDDMMYYYY() to created_at field (from task)
  - Apply DateFormatter::formatToDDMMYYYY() to updated_at field (from task)
  - Apply DateFormatter::formatToDDMMYYYY() to start_date and end_date fields (from task)
  - Ensure null timestamps remain null in response
  - _Requirements: 3.1, 3.2, 3.3_

- [x] 5.1 Return task ID instead of site ID
  - Use `$task->id` for the `id` field in the response
  - Add `site_id` field separately using `$site->id`

- [x] 5.2 Add start_date and end_date from streetlight_tasks
  - Include `start_date` formatted as dd/mm/yyyy from task record
  - Include `end_date` formatted as dd/mm/yyyy from task record

- [x] 5.3 Use task timestamps for created_at and updated_at
  - `created_at` sourced from `streetlight_tasks.created_at` (not site)
  - `updated_at` sourced from `streetlight_tasks.updated_at` (not site)

- [x] 5.4 Calculate total_poles from allotted_wards
  - Replace site's `total_poles` with dynamic calculation: count of allotted wards * 10
  - Split `allotted_wards` by comma, trim whitespace, filter empty entries, multiply count by 10
  - Return 0 when `allotted_wards` is null

- [ ] 6. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Write property tests for response structure
  - [ ] 7.1 Write property test for response status field
    - **Property 5: Response Status Field**
    - **Validates: Requirements 4.1**
    - Generate random valid vendor IDs
    - Verify response contains status: "success"

  - [ ] 7.2 Write property test for vendor ID echo
    - **Property 6: Vendor ID Echo**
    - **Validates: Requirements 4.2**
    - Generate random vendor IDs
    - Verify response vendor_id equals request vendor_id

  - [ ] 7.3 Write property test for sites array structure
    - **Property 7: Sites Array Structure**
    - **Validates: Requirements 4.3**
    - Generate random vendors with and without tasks
    - Verify response has sites field that is an array

  - [ ] 7.4 Write property test for required fields presence
    - **Property 8: Required Fields Presence**
    - **Validates: Requirements 4.4**
    - Generate random tasks with various site data
    - Verify each site object has all 16 required fields

  - [ ] 7.5 Write property test for site ID mapping
    - **Property 9: Site ID Mapping**
    - **Validates: Requirements 4.5**
    - Generate random tasks with known site_ids
    - Verify response id field equals task's site_id, not task id

- [ ] 8. Write property tests for error handling
  - [ ] 8.1 Write property test for orphaned task exclusion
    - **Property 10: Orphaned Task Exclusion**
    - **Validates: Requirements 5.3**
    - Generate mix of valid tasks and tasks with invalid site_ids
    - Verify only tasks with valid sites appear in response

  - [ ] 8.2 Write property test for HTTP status code
    - **Property 11: HTTP Status Code**
    - **Validates: Requirements 5.4**
    - Generate random valid vendor IDs
    - Verify HTTP response status is 200

- [ ] 9. Write unit tests for edge cases and error scenarios
  - [ ] 9.1 Write unit test for vendor with no tasks
    - Test valid vendor with no assigned tasks returns empty sites array
    - Verify status is "success" and HTTP status is 200
    - _Requirements: 5.2, 5.4_

  - [ ] 9.2 Write unit test for non-existent vendor
    - Test invalid vendor ID returns appropriate error response
    - _Requirements: 5.1_

  - [ ] 9.3 Write unit test for orphaned task handling
    - Test task with invalid site_id is excluded from response
    - Verify other valid tasks are still returned
    - _Requirements: 5.3_

  - [ ] 9.4 Write unit test for special characters in allotted_wards
    - Test allotted_wards with commas, spaces, unicode characters
    - Verify exact preservation of format
    - _Requirements: 2.4_

- [ ] 10. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property-based tests validate universal correctness properties across many generated inputs
- Unit tests validate specific examples and edge cases
- The implementation uses PHP/Laravel with Eloquent ORM
- Property-based testing will use eris/eris or phpunit-quickcheck library
- All database changes use existing tables and relationships (no migrations needed)
