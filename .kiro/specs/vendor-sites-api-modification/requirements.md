# Requirements Document

## Introduction

This document specifies the requirements for modifying the existing Vendor Sites API endpoint (`/vendor/{id}/sites`) to change its data source and response format. The endpoint currently returns sites from the `streetlights` table with unique panchayats per vendor. The modification will change the data source to use `streetlight_tasks` table, allowing the same panchayat to appear multiple times if assigned through different tasks, replace ward information with task-specific allotted wards, and format dates in dd/mm/yyyy format instead of ISO format.

## Glossary

- **Vendor_Sites_API**: The REST API endpoint at `/vendor/{id}/sites` that returns site information for a specific vendor
- **Streetlight_Task**: A work assignment record in the `streetlight_tasks` table that links a panchayat/site to field staff (engineer, vendor, project manager)
- **Streetlight_Site**: A panchayat/ward record in the `streetlights` table representing a physical installation location
- **Allotted_Wards**: A text field in `streetlight_tasks` table containing comma-separated ward identifiers assigned to a specific task
- **Panchayat**: An administrative unit (village council) that can have multiple tasks assigned to the same vendor
- **API_Response**: The JSON response object returned by the Vendor Sites API endpoint
- **Date_Formatter**: A component responsible for converting ISO timestamp format to dd/mm/yyyy format

## Requirements

### Requirement 1: Modify Data Source to Use Streetlight Tasks

**User Story:** As an API consumer, I want the vendor sites endpoint to return data from streetlight tasks, so that I can see all task assignments for a vendor including duplicate panchayats with different ward allocations.

#### Acceptance Criteria

1. WHEN the Vendor_Sites_API receives a request for vendor ID, THE Vendor_Sites_API SHALL query the `streetlight_tasks` table filtered by `vendor_id`
2. THE Vendor_Sites_API SHALL join `streetlight_tasks` with `streetlights` table using the `site_id` foreign key relationship
3. THE Vendor_Sites_API SHALL include all Streetlight_Task records for the vendor without filtering duplicate panchayats
4. WHEN multiple Streetlight_Task records reference the same Streetlight_Site, THE Vendor_Sites_API SHALL return separate entries for each task
5. THE Vendor_Sites_API SHALL include the `project_id` from the Streetlight_Task record in each response entry

### Requirement 2: Replace Ward Field with Allotted Wards

**User Story:** As an API consumer, I want to see task-specific allotted wards instead of site-level ward information, so that I can understand which wards are assigned to each specific task.

#### Acceptance Criteria

1. THE Vendor_Sites_API SHALL retrieve the `allotted_wards` value from the `streetlight_tasks` table for each task
2. THE Vendor_Sites_API SHALL include the `allotted_wards` value in the `ward` field of the API_Response
3. WHEN the `allotted_wards` field is NULL in the database, THE Vendor_Sites_API SHALL return NULL or empty string in the `ward` field
4. THE Vendor_Sites_API SHALL preserve the original comma-separated format of the `allotted_wards` value without modification

### Requirement 3: Format Dates as DD/MM/YYYY

**User Story:** As an API consumer, I want dates formatted as dd/mm/yyyy, so that dates are displayed in a human-readable format consistent with regional conventions.

#### Acceptance Criteria

1. THE Date_Formatter SHALL convert the `created_at` timestamp from ISO format to dd/mm/yyyy format
2. THE Date_Formatter SHALL convert the `updated_at` timestamp from ISO format to dd/mm/yyyy format
3. WHEN a timestamp is NULL, THE Date_Formatter SHALL return NULL in the formatted output
4. THE Date_Formatter SHALL use two-digit day and month values with leading zeros (e.g., "05/03/2025" not "5/3/2025")
5. THE Date_Formatter SHALL use four-digit year values

### Requirement 4: Maintain Response Structure Compatibility

**User Story:** As an API consumer, I want the response structure to remain consistent with the current format, so that existing integrations continue to work without breaking changes.

#### Acceptance Criteria

1. THE API_Response SHALL include a `status` field with value "success" for successful requests
2. THE API_Response SHALL include a `vendor_id` field containing the requested vendor identifier
3. THE API_Response SHALL include a `sites` array containing site objects
4. THE Vendor_Sites_API SHALL include all existing fields in each site object: `id`, `district_code`, `block_code`, `panchayat_code`, `state`, `district`, `block`, `panchayat`, `ward`, `mukhiya_contact`, `number_of_surveyed_poles`, `number_of_installed_poles`, `created_at`, `updated_at`, `project_id`, `total_poles`
5. THE Vendor_Sites_API SHALL use the Streetlight_Site `id` field for the site object `id` value

### Requirement 5: Handle Invalid Vendor Requests

**User Story:** As an API consumer, I want clear error responses for invalid vendor IDs, so that I can handle errors appropriately in my application.

#### Acceptance Criteria

1. WHEN the vendor ID does not exist in the system, THE Vendor_Sites_API SHALL return an appropriate error response
2. WHEN the vendor has no assigned Streetlight_Task records, THE Vendor_Sites_API SHALL return an empty `sites` array with status "success"
3. WHEN the Streetlight_Task references a non-existent Streetlight_Site, THE Vendor_Sites_API SHALL exclude that task from the response
4. THE Vendor_Sites_API SHALL return HTTP status code 200 for successful requests with valid vendor IDs
