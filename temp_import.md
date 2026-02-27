# Feature Disable and Device Import modifications for Project 19

The requested changes to restrict certain system operations exclusively for `project_id = 19` have been successfully implemented. 
Below is a summary of all changes made.

## Verification Summary
Verified that the disabled class and attributes are conditionally applied to UI elements when `$project->id == 19`. 
Reviewed controller constraints for device import and confirmed validations are bypassed when `$project->id == 19` using a specific `task_id`, as requested.

### UI Modifications (Disabled Features)

1. **Dispatch:**
   - Disabled the bulk upload "Process Upload" button.
   - Disabled the "Issue Items" button.
   - Files changed: `resources/views/stores/show.blade.php`

2. **Site Import:**
   - Modified the general `x-datatable` Blade component to accept an optional `importDisabled` and `bulkDeleteDisabled` property.
   - Passed these properties to site lists and targets effectively disabling import for `$project->id == 19`.
   - Files changed: `resources/views/components/datatable.blade.php`, `resources/views/projects/project_site.blade.php`

3. **Target Allotment & Action Buttons:**
   - Disabled the "Add Target" button for project 19.
   - Passed `null` for `deleteRoute` and `editRoute` for targets, successfully hiding "Edit" and "Delete" per-row actions.
   - Disabled the dynamically created JS "Reassign Selected" bulk action.
   - Files changed: `resources/views/projects/project_task_streetlight.blade.php`

4. **Staff & Vendor Assignment:**
   - Disabled all "Assign Selected", "Remove Selected" bulk actions.
   - Disabled all individual row "Assign" and "Remove" actions.
   - Files changed: `resources/views/projects/project_staff.blade.php`, `resources/views/projects/project_vendors.blade.php`

### Backend Logic Modifications

1. **Device Import Validation Skip (`project_id = 19` only):**
   - Import restricted to ONLY work with `project_id = 19` per previous specification logic.
   - Bypassed target logic: If no target is assigned currently, it proceeds with a specific `task_id` (originally `0`, now `96492409`).
   - Skipped all Inventory checking & Material Dispatch status code blocks (`validateAndDispatchInventory`).
   - Skipped material consumption code.
   - Files changed: `app/Imports/StreetlightPoleImport.php`
