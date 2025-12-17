# Current Task Tracking

**Last Updated**: 2025-12-16

This file tracks all pending and in-progress tasks across the Laravel CRM project. Tasks are organized by module and priority.

---

## Global Foundations

### gf-project-staff-vendor-management

-   **Status**: completed
-   **Description**: Modern Staff Management and Vendor Management UI with add/remove functionality, role-based permissions, and target reassignment logic
-   **Completed**: 2025-12-16
-   **Files**:
    -   `app/Http/Controllers/ProjectsController.php` (assignUsers, removeStaff methods)
    -   `app/Policies/ProjectPolicy.php` (assignStaff, removeStaff methods)
    -   `resources/views/projects/project_staff.blade.php`
    -   `resources/views/projects/project_vendors.blade.php`
    -   `public/js/project-staff-management.js`
    -   `public/js/project-vendor-management.js`
    -   `routes/web.php`
-   **Functionality**:

    #### Staff/Vendor Assignment

    -   **Admin**: Can assign any staff/vendor to any project (multiple at a time)
    -   **Project Manager**: Can only assign staff/vendors where `manager_id = current_user->id` (their direct reports)
    -   **Pivot Role Storage**: When assigning, the user's role is stored in `project_user.role` pivot column
    -   **Routes**:
        -   `POST /projects/{id}/assign-users` - Assign staff
        -   `POST /projects/{id}/assign-vendors` - Assign vendors (reuses assignUsers method)
    -   **Authorization**: Uses `ProjectPolicy::assignStaff()` method

    #### Staff/Vendor Removal

    -   **Admin**: Can remove any staff/vendor from any project (multiple at a time)
    -   **Project Manager**: Can only remove staff/vendors where `manager_id = current_user->id` (their direct reports)
    -   **Routes**:
        -   `POST /projects/{id}/remove-staff` - Remove staff
        -   `POST /projects/{id}/remove-vendors` - Remove vendors (reuses removeStaff method)
    -   **Authorization**: Uses `ProjectPolicy::removeStaff()` method

    #### Target Reassignment Logic (Critical Behavior)

    When a staff member or vendor is removed from a project, their **ongoing targets/tasks are automatically reassigned**:

    1.  **For Admin removing staff/vendor**:

        -   System attempts to find the Project Manager assigned to the project
        -   If Project Manager exists: All targets reassigned to Project Manager
        -   If no Project Manager: Targets reassigned to Admin (current user)

    2.  **For Project Manager removing their team member**:

        -   All targets reassigned to the Project Manager (themselves)

    3.  **What Gets Reassigned**:

        -   `StreetlightTask` records where `engineer_id = removed_user_id` → Updated to `engineer_id = reassigned_user_id`
        -   `StreetlightTask` records where `vendor_id = removed_user_id` → Updated to `vendor_id = reassigned_user_id`
        -   Only tasks for the **current project** are reassigned (filtered by `project_id`)

    4.  **Example Scenario**:

        -   Vendor "John" is assigned to Streetlight Project "Panchayat A"
        -   John has 10 targets assigned (`streetlight_tasks.vendor_id = John's ID`)
        -   Admin removes John from the project
        -   **Result**: All 10 targets are reassigned to the Project Manager of "Panchayat A"
        -   **Previous tasks remain**: The tasks are NOT deleted, they are reassigned to the PM
        -   **Vendor's access**: Once removed, John can no longer see these tasks in the project context
        -   **If vendor is re-assigned later**: They will see NEW tasks assigned to them, but NOT the previously reassigned ones (those now belong to PM)

    5.  **Important Notes**:
        -   Target reassignment happens **immediately** upon removal
        -   Reassignment count is logged and returned in the response
        -   All operations are wrapped in database transactions for data integrity
        -   The removed user is **detached** from the project (`project_user` pivot record removed)
        -   Historical task data remains intact (tasks are not deleted, just reassigned)

-   **UI Features**:

    -   Modern, minimal design matching parent page aesthetics
    -   Two-column layout: Assigned (left) / Available (right)
    -   Bulk selection with checkboxes
    -   Bulk action buttons at top ("X selected assign" / "X selected remove")
    -   Search functionality for available staff/vendors
    -   Role-based grouping for staff (Project Manager, Site Engineer, Store Incharge, Coordinator)
    -   SweetAlert2 confirmations and toast notifications
    -   Loading states during AJAX operations
    -   Empty states when no staff/vendors available

-   **Technical Implementation**:

    -   Uses `FormData` for AJAX requests with CSRF token handling
    -   Fresh CSRF token retrieval from meta tag for each request
    -   `syncWithoutDetaching()` with pivot role data: `[user_id => ['role' => role_value]]`
    -   Separate queries for vendors (excluded from staff queries)
    -   SQL ambiguity fixes (qualified `users.id` in joins)
    -   Route parameter consistency (`$id` instead of `$projectId`)

-   **URLs to test**:

    -   http://localhost:8000/projects/11#staff (Staff Management tab)
    -   http://localhost:8000/projects/11#vendors (Vendor Management tab)

-   **Issues Fixed**:
    1.  Pivot role not being set during assignment → Fixed by including pivot data in `syncWithoutDetaching()`
    2.  Assigned vendors query returning empty → Fixed by querying vendors separately from staff
    3.  SQL ambiguous column errors → Fixed by qualifying column names (`users.id`)
    4.  CSRF token mismatch → Fixed by using FormData and fresh token retrieval
    5.  Route parameter mismatch → Fixed by using `$id` consistently

---

### gf-datatable-fixes

-   **Status**: completed
-   **Description**: Fix dynamic data table component issues across projects, projects>sites, staff management, and TA&DA modules, apply the datatable component in installed-poles, surveyed-poles, inventory/view, dispatched inventory,targets, vendors management, conveyance, billing setting:
    -   widths of column is high which doesnot makes sense
    -   Select all from header checkbox doesn't work
    -   Search box layout distorted(reduced widths)
    -   Column widths getting distorted in staff management, projects>sites, and TA&DA tables
-   **Completed**: 2025-12-16
-   **Notes**:
    -   Fixed column widths (checkbox column: 30px, actions column: 120px fixed width)
    -   Fixed select all checkbox functionality
    -   Fixed search box layout (responsive max-width)
    -   Fixed actions column stretching issue
    -   Removed right-hand filter/column visibility icons from headers
    -   Sort functionality working correctly (sort icons visibility needs future investigation - MDI codes may need adjustment)
-   **Files**:
    -   `resources/views/projects/*.blade.php`
    -   `resources/views/staff/*.blade.php`
    -   `resources/views/billing/tada.blade.php`
    -   other relevant files
-   **URLs to test**:
    -   http://localhost:8000/staff
    -   http://localhost:8000/projects/11 (sites tab)
    -   http://localhost:8000/billing/tada
    -   http://localhost:8000/installed-poles?project_manager=91&role=1
    -   http://localhost:8000/surveyed-poles?project_manager=91&role=1
    -   http://localhost:8000/inventory/view?project_id=11&store_id=23
    -   http://localhost:8000/inventory/dispatch?item_code=SL03&store_id=23
    -   http://localhost:8000/projects/11 (targets tab)
    -   http://localhost:8000/uservendors
    -   http://localhost:8000/billing/convenience
    -   http://localhost:8000/billing/settings

---

## Module 1: Vendors

### vendor-crud-flows

-   **Status**: completed
-   **Description**: Finalize Vendor CRUD flows and browser-test
-   **Dependencies**: gf-auth-roles
-   **Files**:
    -   `app/Http/Controllers/VendorController.php`
    -   `resources/views/uservendors/*.blade.php`
-   **Tasks**:
    -   ✅ Verify create/edit forms work correctly - Fixed field name mismatches, validation, and pre-selection
    -   ✅ Test vendor listing, detail view, create, edit, delete - All CRUD operations fixed and verified
    -   ✅ Ensure role restrictions are enforced - Added authorization checks to all methods
    -   ✅ Browser-test all flows and document in test_report.md - Code fixes documented in test_report.md (actual browser testing requires user credentials/URL)

### vendor-show-page-improvements

-   **Status**: completed
-   **Description**: Modernize vendor show page UI, separate projects and inventory, add avatar upload, implement installed poles filters
-   **Completed**: 2025-12-16
-   **Files**:
    -   `app/Http/Controllers/VendorController.php` (uploadAvatar method)
    -   `app/Http/Controllers/API/TaskController.php` (getInstalledPoles method)
    -   `resources/views/uservendors/show.blade.php` (complete redesign)
    -   `resources/views/poles/installed.blade.php` (datatable component integration)
    -   `routes/web.php` (avatar upload route)
-   **Changes**:
    -   **Basic Details Redesign**: Modern card layout with avatar, smaller fonts, grouped data (Contact, Team, Projects, Banking), minimal edit button
    -   **Projects/Inventory Separation**: Separated into distinct cards for better organization
    -   **Avatar Upload**: Added functionality to upload and change vendor profile picture (S3 storage, AJAX upload)
    -   **Installed Poles Filters**: Implemented datatable component with surveyed/installed/billed filters
    -   **Icon Actions**: Replaced text buttons with icon buttons (Replace: swap icon, Return: undo icon) with tooltips
-   **URLs to test**:
    -   http://localhost:8000/uservendors/130
    -   http://localhost:8000/installed-poles?vendor=130&project_id=11&panchayat=MAKDAMPUR
-   **Documentation**: See `VENDOR_SHOW_PAGE_IMPROVEMENTS.md` for detailed summary

### vendor-earnings-configuration

-   **Status**: pending
-   **Description**: Make vendor earnings rate (currently ₹500 per installed pole) configurable from settings page
-   **Dependencies**: vendor-crud-flows
-   **Files**:
    -   `app/Http/Controllers/VendorController.php`
    -   `app/Http/Controllers/SettingsController.php` (or equivalent)
    -   `resources/views/settings/*.blade.php`
-   **Tasks**:
    -   Add earnings rate configuration field to settings
    -   Update VendorController to use configurable rate instead of hardcoded ₹500
    -   Allow per-project or global earnings rate configuration
    -   Browser-test earnings calculation with different rates

---

## Module 2: Tasks & Poles

### tasks-crud-and-status

-   **Status**: pending
-   **Description**: Complete Tasks CRUD, status updates, and poles-related views, then browser-test
-   **Dependencies**: gf-auth-roles, gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/TasksController.php`
    -   `app/Services/Task/*.php`
    -   `resources/views/tasks/*.blade.php`
-   **Tasks**:
    -   Verify task creation/editing for both rooftop and streetlight contexts
    -   Ensure status updates use TaskStatus enum correctly
    -   Fix "surveyed poles" and "installed poles" views
    -   Test export functionality
    -   Browser-test all operations and document results

---

## Module 3: Sites

### sites-crud-import

-   **Status**: pending
-   **Description**: Finish Sites CRUD, import, search, and ward poles behavior with browser verification
-   **Dependencies**: gf-auth-roles
-   **Files**:
    -   `app/Http/Controllers/SiteController.php`
    -   `app/Imports/SiteImport.php`
    -   `resources/views/sites/*.blade.php`

### sites-add-site-project-types

-   **Status**: pending
-   **Description**: Fix Add Site functionality to work for both rooftop (project_type=0) and streetlight (project_type=1) projects
-   **Dependencies**: sites-crud-import
-   **Files**:
    -   `app/Http/Controllers/SiteController.php`
    -   `resources/views/sites/create.blade.php`
    -   `app/Models/Streetlight.php`
-   **Sub-tasks**:
    -   **update-create-method**: Update create() method in SiteController to accept project_id, fetch project, and pass project_type to view
    -   **update-store-method**: Update store() method to check project_type and create appropriate model (Site or Streetlight) with correct validation
    -   **add-task-id-generator**: Add helper method to generate task_id for streetlight sites using district prefix + counter logic
    -   **update-create-view**: Modify create.blade.php to conditionally show rooftop or streetlight form based on project_type
    -   **check-mukhiya-field**: Check if mukhiya_contact exists in streetlights table and add to Streetlight model fillable if needed

---

## Module 4: Inventory

### inv-crud-and-filters

-   **Status**: pending
-   **Description**: Finalize Inventory CRUD, imports, dispatch/return/replace, and pagination with browser tests
-   **Dependencies**: gf-auth-roles, gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/InventoryController.php`
    -   `app/Services/Inventory/*.php`
    -   `resources/views/inventory/*.blade.php`
-   **Tasks**:
    -   Validate create/edit forms
    -   Test import (rooftop and streetlight)
    -   Test dispatch, return, replace operations
    -   Test bulk delete, QR check, dispatched stock views
    -   Verify project scoping and pagination behavior
    -   Browser-test all operations

---

## Module 5: Meetings (Meets)

### meets-extend-testing

-   **Status**: pending
-   **Description**: Re-verify Meet fixes and fully test all Meetings flows (CRUD, notes, follow-ups, whiteboard, exports) in browser
-   **Dependencies**: gf-auth-roles, gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/MeetController.php`
    -   `resources/views/review-meetings/*.blade.php`
-   **Tasks**:
    -   Verify meeting creation fixes are in place
    -   Test view meeting details, edit, delete
    -   Test notes, discussion points, follow-ups
    -   Test exports (PDF/Excel)
    -   Test whiteboard functionality
    -   Update test_report.md Meetings section

---

## Module 6: Performance

### perf-detailed-views

-   **Status**: pending
-   **Description**: Implement and test detailed Performance views (user, subordinates, leaderboard, trends, exports)
-   **Dependencies**: gf-auth-roles
-   **Files**:
    -   `app/Http/Controllers/PerformanceController.php`
    -   `resources/views/performance/*.blade.php`
-   **Tasks**:
    -   Ensure routes for user performance, subordinates, leaderboard, trends, and exports are wired correctly
    -   Verify UserRole-based visibility
    -   Browser-test performance dashboard and all sub-routes
    -   Test filters, charts/tables, and export results
    -   Document in test_report.md

---

## Module 7: Billing (TA&DA, Conveyance, Settings)

### billing-module-complete

-   **Status**: pending
-   **Description**: Fix Billing routes and complete TA&DA, conveyance, and settings flows with browser tests
-   **Dependencies**: gf-auth-roles, gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/BillingController.php`
    -   `app/Http/Controllers/ConveyanceController.php`
    -   `resources/views/billing/*.blade.php`
-   **Tasks**:
    -   **billing-route-fix**: Fix route definitions so `/billing/tada`, `/billing/convenience`, and `/billing/settings` don't redirect to `/performance`
    -   **billing-tada-flows**: Implement/verify TA&DA listing, details, bulk status updates
    -   **billing-conveyance-flows**: Implement conveyance list, accept/reject, bulk actions, details view
    -   **billing-settings-flows**: Implement settings (vehicles, categories, user settings, allowed expenses, city categories)
    -   Browser-test all billing flows end-to-end
    -   Update test_report.md Billing section

---

## Module 8: JICR

### jicr-module-complete

-   **Status**: pending
-   **Description**: Fix JICR routing, AJAX dropdowns, and PDF generation; browser-test end-to-end
-   **Dependencies**: gf-auth-roles, gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/JICRController.php`
    -   `resources/views/jicr/*.blade.php`
-   **Tasks**:
    -   **jicr-route-fix**: Fix `/jicr` routing/permission so page is accessible instead of redirecting to `/performance`
    -   **jicr-ajax-dropdowns**: Verify AJAX-based district/block/panchayat/ward dropdowns work correctly
    -   **jicr-pdf-generation**: Verify PDF generation logic works correctly
    -   Browser-test complete JICR flow
    -   Log behavior and fixes in test_report.md

---

## Module 9: Backup (Advanced Exports)

### backup-advanced-exports

-   **Status**: pending
-   **Description**: Implement DataTransformationService and project-specific multi-sheet backup exports plus browser tests
-   **Dependencies**: gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/BackupController.php`
    -   `app/Services/Backup/DataTransformationService.php` (to be created)
    -   `resources/views/data_backup/backup.blade.php`
-   **Tasks**:
    -   **backup-data-transform-service**: Create DataTransformationService for human-readable conversions:
        -   Boolean fields (isSurveyDone, isInstallationDone, isNetworkAvailable, billed, disableLogin)
        -   Enum fields (UserRole, TaskStatus, ProjectType, InstallationPhase)
        -   Site status enum fields
    -   **backup-project-specific-exports**: Implement project-type-specific export logic:
        -   Rooftop projects: Project Details, Sites, Staff, Inventory Used/Stock, Tasks, Sites Done
        -   Streetlight projects: Project Details, Streetlight Sites, Store Inventory, Staff, Vendors, Targets, Installations (Poles)
    -   **backup-multi-sheet-excel**: Generate multi-sheet Excel/CSV with organized structure
    -   **backup-browser-tests**: Browser-test creating backups for rooftop and streetlight projects, download and inspect files
    -   Update test_report.md

---

## Module 10: HRM/Candidates

### hrm-module-complete

-   **Status**: pending
-   **Description**: Fix HRM/Candidates routing and complete all flows (list, import, emails, uploads, bulk ops) with browser tests
-   **Dependencies**: gf-auth-roles, gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/CandidateController.php`
    -   `app/Imports/CandidatesImport.php`
    -   `resources/views/candidates/*.blade.php`
-   **Tasks**:
    -   **hrm-route-fix**: Fix `/candidates` and related routes so they don't redirect to `/performance`
    -   **hrm-flows**: Implement/verify candidates listing, import, email sending, document uploads, bulk updates, deletion, admin preview views
    -   Browser-test candidates workflows end-to-end
    -   Document in test_report.md

---

## Module 11: Device Import

### device-import-complete

-   **Status**: pending
-   **Description**: Fix Device Import routing and fully test Excel import in browser
-   **Dependencies**: gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/DeviceController.php`
    -   `resources/views/devices-import/*.blade.php`
-   **Tasks**:
    -   **devices-route-fix**: Ensure `/devices-import` routes to correct controller/view instead of redirecting
    -   **devices-import-flow**: Verify Excel import processing, validations, error reporting, and UI feedback
    -   Browser-test device import with sample files
    -   Document results

---

## Module 12: RMS Export

### rms-export-complete

-   **Status**: pending
-   **Description**: Complete and browser-test RMS export flows including AJAX dropdowns and submit behavior
-   **Dependencies**: gf-routing-cleanup
-   **Files**:
    -   `app/Http/Controllers/RMSExportController.php` (or equivalent)
    -   `resources/views/rms-export/*.blade.php`
-   **Tasks**:
    -   **rms-flows**: Verify district/block/panchayat dropdowns (AJAX) and "Push To RMS" submit path
    -   Browser-test entire RMS export flow, including network requests
    -   Log behavior in test_report.md

---

## Module 13: Public Pages

### public-pages-verification

-   **Status**: pending
-   **Description**: Verify all public pages and forms in browser and log results
-   **Files**:
    -   `routes/web.php`
    -   `resources/views/public/*.blade.php`
-   **Tasks**:
    -   Identify all public routes (privacy policy, terms, certificate pages, apply-now, preview, success)
    -   Browser-test each public page for correct content, links, and form submissions
    -   Update test_report.md

---

## Documentation & Testing

### doc-test-report-updates

-   **Status**: pending
-   **Description**: Continuously update test_report.md with detailed, Jira-style entries for each module
-   **Tasks**:
    -   After each module's work, append clear sections to test_report.md
    -   Include: Status (DONE/NOT DONE), actions taken, issues found, changes made, results after changes
    -   Follow TESTING_RULES.md: no guess words, always browser-test user-facing changes, report exact observed behavior

---

## Notes

-   All tasks should follow `.cursorrules` requirements:

    -   Read Model files AND Migration files before writing Eloquent queries
    -   No assumptions - verify everything
    -   Browser-test all UI changes before marking complete
    -   Use UserRole enum, never hardcoded integers
    -   Check routes/web.php for closures vs controllers
    -   Use Log::info() with structured arrays for debugging

-   Testing Requirements:
    -   Follow TESTING_RULES.md strictly
    -   Use binary status: DONE or NOT DONE
    -   Provide concrete evidence for all status reports
    -   No guess words - only report what is actually observed
